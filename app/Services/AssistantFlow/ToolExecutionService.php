<?php

namespace App\Services\AssistantFlow;

use Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;

// Importar servicios específicos de las herramientas
use App\Services\TelegramNotifierService;
use App\Services\Quotes\QuoteRequestService; // Asegúrate de la ruta correcta

// Si validateArguments está en ThreadRunnerService, necesitarás inyectarlo o moverlo.
use App\Services\ThreadRunnerService; // Si es necesario para validateArguments

// Nuevo servicio para el manejo de caché
use App\Services\AssistantFlow\CacheManagerService;

class ToolExecutionService
{
    protected ThreadRunnerService $threadRunnerService; // Para validateArguments
    protected TelegramNotifierService $telegramNotifier;
    protected QuoteRequestService $quoteRequestService;
    protected CacheManagerService $cacheManagerService;

    public function __construct(
        ThreadRunnerService $threadRunnerService, // Asumiendo que validateArguments está aquí
        TelegramNotifierService $telegramNotifier,
        QuoteRequestService $quoteRequestService,
        CacheManagerService $cacheManagerService
    ) {
        $this->threadRunnerService = $threadRunnerService;
        $this->telegramNotifier = $telegramNotifier;
        $this->quoteRequestService = $quoteRequestService;
        $this->cacheManagerService = $cacheManagerService;
    }

    /**
     * Procesa las llamadas a herramientas requeridas por un Run y envía sus outputs.
     *
     * @param string $threadId
     * @param ThreadRunResponse $run El objeto Run actual con status 'requires_action'.
     * @throws \Exception Si falla el procesamiento o el envío de outputs.
     */
    public function handleToolCalls(string $threadId, ThreadRunResponse $run, $sessionId): void
    {
        if ($run->status !== 'requires_action') {
            Log::warning('handleToolCalls called for a run not requiring action.', ['run_id' => $run->id, 'status' => $run->status]);
            return;
        }

        $toolOutputs = [];
        $datosVehiculo = $this->cacheManagerService->getVehicleData($threadId); // Cargar datos del vehículo de caché
        $coberturaElegida = $this->cacheManagerService->getCoverageData($threadId); // Cargar cobertura de caché

        Log::info('Datos recuperados de caché al inicio de handleToolCalls', [
            'thread_id' => $threadId, 
            'datosVehiculo' => $datosVehiculo ? 'present' : 'missing', 
            'coberturaElegida' => $coberturaElegida ? 'present' : 'missing'
        ]);

        foreach ($run->requiredAction->submitToolOutputs->toolCalls as $toolCall) {
            Log::info('Processing toolCall', ['tool_call_id' => $toolCall->id ?? 'N/A', 'function_name' => $toolCall->function->name ?? 'N/A', 'arguments' => $toolCall->function->arguments ?? 'N/A']);

            if (!isset($toolCall->function->name)) {
                Log::warning('Tool call function name is missing, skipping.', ['tool_call' => $toolCall]);
                continue;
            }

            $outputPayload = null;
            
            try {
                switch ($toolCall->function->name) {
                    case 'obtener_datos_vehiculo':
                        $datosVehiculo = $this->threadRunnerService->validateArguments($toolCall->function->arguments);
                        Log::info('Funcion obtener_datos_vehiculo: Argumentos validados', ['datosVehiculo' => $datosVehiculo]);

                        // Almacenar los datos del vehículo en caché
                        $this->cacheManagerService->putVehicleData($threadId, $datosVehiculo); 
                        Log::info('Datos del vehículo guardados en caché', ['thread_id' => $threadId, 'datosVehiculo' => $datosVehiculo]);

                        $outputPayload = json_encode([
                            'status' => 'success',
                            'vehicle_data' => $datosVehiculo
                        ]);
                        break;
                    case 'cobertura_a':
                    case 'cobertura_b':
                    case 'cobertura_c':
                    case 'cobertura_d':
                        // Asegurarse de tener los datos del vehículo para la cotización
                        if (!$datosVehiculo) {
                            Log::error('Datos del vehículo no encontrados en caché para cotización de cobertura.', ['thread_id' => $threadId, 'tool_call' => $toolCall->function->name]);
                            $outputPayload = json_encode(['status' => 'error', 'message' => 'Faltan datos del vehículo para cotizar. Por favor, reiniciá la conversación.']);
                            break; 
                        }
                        
                        $coberturaElegida = $toolCall->function->name; // El nombre de la función es el nombre de la cobertura
                        // Almacenar la cobertura elegida en caché
                        $this->cacheManagerService->putCoverageData($threadId, $coberturaElegida);
                        Log::info('Cobertura elegida guardada en caché', ['thread_id' => $threadId, 'coberturaElegida' => $coberturaElegida]);
                        
                        // NOTA: Guardar Quote y Enviar Telegram se hacen *después* de submitToolOutputs.
                        // La respuesta para el asistente aquí es solo para que sepa que la acción de "elegir cobertura" fue exitosa.
                        $outputPayload = json_encode([
                            'status' => 'success',
                            'message_for_user' => "Perfecto! Dame unos minutitos que te preparo la cotización.",
                        ]);
                        break;
                    default:
                        Log::warning('Unknown tool call function (ToolExecutionService)', ['function_name' => $toolCall->function->name]);
                        $outputPayload = json_encode(['status' => 'error', 'message' => 'unknown_tool_function']);
                        break;
                }
            } catch (\Exception $e) {
                Log::error('Error executing tool function', ['function_name' => $toolCall->function->name, 'error' => $e->getMessage()]);
                $outputPayload = json_encode(['status' => 'error', 'message' => 'Error ejecutando función: ' . $e->getMessage()]);
            }
            
            if ($outputPayload !== null) {
                $toolOutputs[] = [
                    'tool_call_id' => $toolCall->id,
                    'output' => $outputPayload,
                ];
            } else {
                Log::error('Tool output payload was null after processing', ['tool_call' => $toolCall]);
            }
        }

        if (!empty($toolOutputs)) {
            Log::info('Submitting tool outputs to OpenAI from ToolExecutionService', ['outputs' => $toolOutputs, 'run_id' => $run->id]);
            try {
                // --- PUNTO CLAVE: SubmitToolOutputs ---
                OpenAI::threads()->runs()->submitToolOutputsStreamed(
                    threadId: $threadId,
                    runId: $run->id,
                    parameters: ['tool_outputs' => $toolOutputs]
                );
                Log::info('OpenAI tool outputs submission successful from ToolExecutionService', ['run_id' => $run->id]);

                // --- NUEVA LÓGICA DE NEGOCIO AQUÍ: Guardar Quote y Enviar Telegram ---
                // Se ejecuta *después* de que OpenAI ha recibido las salidas de las herramientas.
                
                // 1. Obtener los datos necesarios de caché
                $finalVehicleData = $this->cacheManagerService->getVehicleData($threadId);
                $finalCoverageChosen = $this->cacheManagerService->getCoverageData($threadId);
                
                $quoteCreationResult = null;
                $telegramSent = null;

                // 2. Ejecutar la creación de la cotización SOLO si tenemos ambos datos
                if ($finalVehicleData && $finalCoverageChosen) {
                    $quoteCreationResult = $this->quoteRequestService->crearSolicitud($finalVehicleData, $finalCoverageChosen, $sessionId);
                    Log::info('Quote Request creado', ['quote_result' => $quoteCreationResult]);

                    if ($quoteCreationResult && $quoteCreationResult['success']) {
                        $quoteIdForTelegram = $quoteCreationResult['data']->id ?? null; // Usar null si no hay ID
                        
                        // 3. Enviar mensaje por Telegram SOLO si la cotización se guardó con éxito
                        $telegramSent = $this->telegramNotifier->notify(
                            $finalVehicleData, 
                            $finalCoverageChosen,
                            $quoteIdForTelegram // <--- CORRECCIÓN AQUÍ: Pasa solo el ID
                        );
                        Log::info('Mensaje de Telegram enviado', [
                            'sendTelegram_result' => $telegramSent,
                            'quote_id' => $quoteIdForTelegram
                        ]);

                        // Opcional: Limpiar la caché después de que el proceso principal ha terminado
                        // $this->cacheManagerService->forgetVehicleData($threadId);
                        // $this->cacheManagerService->forgetCoverageData($threadId);

                    } else {
                        Log::warning('No se envió el Telegram: la solicitud de quote falló.', [
                            'thread_id' => $threadId,
                            'quote_creation_success' => $quoteCreationResult['success'] ?? false,
                            'quote_creation_message' => $quoteCreationResult['message'] ?? 'Unknown error'
                        ]);
                    }
                } else {
                    Log::warning('No se pudo crear la solicitud de quote ni enviar Telegram: faltan datos del vehículo o cobertura.', [
                        'thread_id' => $threadId,
                        'vehicle_data_present' => (bool) $finalVehicleData,
                        'coverage_chosen_present' => (bool) $finalCoverageChosen
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Failed to submit tool outputs or execute post-submission logic from ToolExecutionService', ['error' => $e->getMessage(), 'run_id' => $run->id, 'outputs_attempted' => $toolOutputs]);
                throw new \Exception('Error en el procesamiento de salidas de herramientas o lógica post-envío: ' . $e->getMessage());
            }
        } else {
            Log::warning('No tool outputs generated for submission in ToolExecutionService', ['run_id' => $run->id]);
        }
    }

    /**
     * Obtiene el precio de una cobertura de forma dinámica.
     * En un entorno real, esto consultaría una BD, un servicio de precios, etc.
     *
     * @param string $coverageName El nombre de la cobertura (ej. 'cobertura_a').
     * @return string El precio de la cobertura.
     */
    protected function getCoveragePrice(string $coverageName): string
    {
        // --- LÓGICA PARA OBTENER PRECIOS DINÁMICAMENTE ---
        // Aquí puedes:
        // 1. Consultar una base de datos:
        //    return \DB::table('coverage_prices')->where('name', $coverageName)->value('price');
        //
        // 2. Leer de un archivo de configuración de Laravel:
        //    return config("coverages.prices.{$coverageName}", '0'); // Define 'coverages.php' en config/
        //
        // 3. Llamar a otro servicio de precios (si lo hubiere):
        //    return $this->priceService->getPrice($coverageName); // Necesitaría inyectar PriceService en el constructor
        //
        // Por ahora, para que el código sea funcional, mantenemos un mapeo simple:
        $prices = [
            'cobertura_a' => '10000',
            'coobertura_b' => '20000',
            'cobertura_c' => '30000',
            'cobertura_d' => '40000',
        ];

        return $prices[$coverageName] ?? '0'; // Retorna 0 si no se encuentra
    }
}
