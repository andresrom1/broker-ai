<?php

namespace App\Services;

use App\Services\Quotes\QuoteRequestService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\Threads\SubmitOutputStreamed;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ThreadRunnerService
{
    protected string $assistantId = 'asst_8XHbWmWOrpTeqKKB23nsk3Hh';
    protected int $timeoutSeconds;
    protected QuoteRequestService $quoteRequest;
    protected SubmitOutputStreamed $submitOutput;

    public function __construct(int $timeoutSeconds = 30){
        $this->timeoutSeconds = $timeoutSeconds;
        $this->quoteRequest = new QuoteRequestService ();
        $this->submitOutput = new SubmitOutputStreamed ();
    }
    /**
     * Valida los datos del fuctionCall y los convierte en array
     * 
     * @param mixed $args
     * @return mixed $vehicleData
     * 
     */
    public function validateArguments($args): array
    {
        Log::info('Args del validador', ['$args', $args]);
        $arguments = json_decode($args, true);
        Log::info('Decoded Agrs', ['$args', $arguments]);

        $validated = Validator::make($arguments, [
            'gnc' => 'nullable|boolean',
            'anio' => 'nullable|int|min:1950|max:'.(date('Y')+1),
            'marca' => 'nullable|max:50',
            'modelo' => 'nullable|string|max:50',
            'version' => 'nullable|string|max:50',
            'codigo_postal' => 'nullable|digits:4'
        ])->validate();
        
        $vehicleData = [
            'marca' => $validated['marca'],
            'modelo' => $validated['modelo'],
            'year' => $validated['anio'],
            'version' => $validated['version'] ?? 'Base',
            'cp' => $validated['codigo_postal'],
            'gnc' => $validated['gnc']
        ];

        return $vehicleData;
    }

    /**
     * Ejecuta un run en el thread, maneja requires_action y devuelve la respuesta final y el runId.
     *
     * @param string $threadId
     * @return mixed
     * @throws \Exception
     */
    // public function executeRun(string $threadId): mixed
    // {
        
    //      $stream = OpenAI::threads()->runs()->createStreamed(
    //         threadId: $threadId,
    //         parameters: [
    //             'assistant_id' => $this->assistantId,
    //         ],
    //     );

    //     Log::info('Stream', ['Stream' => $stream]);

    //     $datosVehiculoCallId = null;
    //     $precioCuotaCallId = null;
    //     $datosVehiculo = null;
    //     $costo = null;

    //     do {
    //         foreach ($stream as $response) {
    //             switch ($response->event) {
    //                 case 'thread.run.created':
    //                 case 'thread.run.queued':
    //                 case 'thread.run.completed':
    //                 case 'thread.run.cancelling':
    //                     $run = $response->response;
    //                     break;
    //                 case 'thread.run.expired':
    //                 case 'thread.run.cancelled':
    //                 case 'thread.run.failed':
    //                     $run = $response->response;
    //                     break 3;
    //                 case 'thread.run.requires_action':

    //                     Log::info('Thread requires action START', ['run_id' => $run->id, 'tool_calls_count' => count($response->response->requiredAction->submitToolOutputs->toolCalls ?? [])]);

    //                     $toolOutputs = [];
    //                     $datosVehiculo = null; // Initialize to null
    //                     $sendTelegram = null; // Initialize to null
    //                     $quote = null; // Initialize quote

    //                     foreach ($response->response->requiredAction->submitToolOutputs->toolCalls as $toolCall) {
    //                         Log::info('Processing toolCall', ['tool_call_id' => $toolCall->id ?? 'N/A', 'function_name' => $toolCall->function->name ?? 'N/A', 'arguments' => $toolCall->function->arguments ?? 'N/A']);

    //                         if (!isset($toolCall->function->name)) {
    //                             Log::warning('Tool call function name is missing, skipping.', ['tool_call' => $toolCall]);
    //                             continue;
    //                         }

    //                         $outputPayload = null; // Variable to hold the output for the current toolCall

    //                         switch ($toolCall->function->name) {
    //                             case 'obtener_datos_vehiculo':
    //                                 try {
    //                                     $datosVehiculo = $this->validateArguments($toolCall->function->arguments);
    //                                     Log::info('Funcion obtener_datos_vehiculo: Argumentos validados', ['datosVehiculo' => $datosVehiculo]);

    //                                     $telegramNotifier = new TelegramNotifierService(); // Instantiate TelegramNotifier here
    //                                     $sendTelegram = $telegramNotifier->notify($datosVehiculo,"","0");
    //                                     Log::info('Funcion obtener_datos_vehiculo: Telegram enviado', ['sendTelegram_result' => $sendTelegram]);

    //                                     $quote = $this->quoteRequest->crearSolicitud($datosVehiculo);
    //                                     Log::info('Funcion obtener_datos_vehiculo: Quote creado', ['quote_result' => $quote]);

    //                                     // *** CAMBIO CLAVE AQUÍ: INCLUIR LOS DATOS DEL VEHÍCULO EN EL OUTPUT ***
    //                                     // El asistente espera los datos que la función "obtuvo" para poder avanzar.
    //                                     $outputPayload = json_encode([
    //                                         'status' => 'success',
    //                                         'telegram_sent' => (bool) $sendTelegram,
    //                                         'quote_created' => (bool) $quote,
    //                                         'vehicle_data' => $datosVehiculo // <--- ¡Esto es lo que el asistente necesita para el STEP 3 y 5!
    //                                     ]);
    //                                     Log::info('Funcion obtener_datos_vehiculo: Output generado', ['outputPayload' => $outputPayload]);

    //                                 } catch (Exception $e) {
    //                                     Log::error('Error en obtener_datos_vehiculo', ['error' => $e->getMessage(), 'tool_call' => $toolCall]);
    //                                     $outputPayload = json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    //                                 }
    //                                 break;
    //                             case 'cobertura_a':
    //                                 Log::info('Funcion cobertura A ha recopilado los datos', ['tool' => $toolCall]);
    //                                 $costo = '10000';
    //                                 // *** CAMBIO CLAVE AQUÍ: OUTPUT MÁS DESCRIPTIVO Y CONVERSACIONAL ***
    //                                 $outputPayload = json_encode([
    //                                     'status' => 'success',
    //                                     'message_for_user' => "La Cobertura Obligatoria (Responsabilidad Civil y Grúa/Auxilio) tiene un costo mensual de \$$costo.",
    //                                     'costo' => $costo // Mantenemos el costo como un campo separado también
    //                                 ]);
    //                                 Log::info('Funcion cobertura A: Output generado', ['outputPayload' => $outputPayload]);
    //                                 break;
    //                             case 'cobertura_b':
    //                                 Log::info('Funcion cobertura B ha recopilado los datos', ['tool' => $toolCall]);
    //                                 $costo = '20000';
    //                                 // *** CAMBIO CLAVE AQUÍ ***
    //                                 $outputPayload = json_encode([
    //                                     'status' => 'success',
    //                                     'message_for_user' => "La Cobertura de Robo e Incendio tiene un costo mensual de \$$costo, con opciones adicionales para explorar.",
    //                                     'costo' => $costo
    //                                 ]);
    //                                 Log::info('Funcion cobertura B: Output generado', ['outputPayload' => $outputPayload]);
    //                                 break;
    //                             case 'cobertura_c':
    //                                 Log::info('Funcion cobertura C ha recopilado los datos', ['tool' => $toolCall]);
    //                                 $costo = '30000';
    //                                 // *** CAMBIO CLAVE AQUÍ ***
    //                                 $outputPayload = json_encode([
    //                                     'status' => 'success',
    //                                     'message_for_user' => "La Cobertura de Terceros Completos tiene un costo mensual de \$$costo, y abarca más coberturas.",
    //                                     'costo' => $costo
    //                                 ]);
    //                                 Log::info('Funcion cobertura C: Output generado', ['outputPayload' => $outputPayload]);
    //                                 break;
    //                             case 'cobertura_d':
    //                                 Log::info('Funcion cobertura D ha recopilado los datos', ['tool' => $toolCall]);
    //                                 $costo = '40000';
    //                                 // *** CAMBIO CLAVE AQUÍ ***
    //                                 $outputPayload = json_encode([
    //                                     'status' => 'success',
    //                                     'message_for_user' => "La Cobertura Todo Riesgo, la más amplia, tiene un costo mensual de \$$costo.",
    //                                     'costo' => $costo
    //                                 ]);
    //                                 Log::info('Funcion cobertura D: Output generado', ['outputPayload' => $outputPayload]);
    //                                 break;
    //                         }

    //                         // Add the output for the current tool call to the array
    //                         if ($outputPayload !== null) {
    //                             $toolOutputs[] = [
    //                                 'tool_call_id' => $toolCall->id,
    //                                 'output' => $outputPayload,
    //                             ];
    //                         } else {
    //                             Log::error('Tool output payload was null for tool call', ['tool_call' => $toolCall]);
    //                         }
    //                     }

    //                     Log::info('Finished processing all tool calls in current requires_action event', ['collected_outputs_count' => count($toolOutputs)]);

    //                     // ... (código anterior) ...

    //                     if (!empty($toolOutputs)) {
    //                         Log::info('Attempting to submit tool outputs to OpenAI', ['outputs' => $toolOutputs, 'thread_id' => $threadId, 'run_id' => $run->id]);
    //                         try {
    //                             $submitResponse = OpenAI::threads()->runs()->submitToolOutputsStreamed(
    //                                 threadId: $threadId,
    //                                 runId: $run->id,
    //                                 parameters: [
    //                                     'tool_outputs' => $toolOutputs,
    //                                 ]
    //                             );
    //                             Log::info('OpenAI tool outputs submission successful', ['response_object_type' => get_class($submitResponse)]);

    //                             // *** AÑADIR UN PEQUEÑO RETRASO AQUÍ PARA LA SINCRONIZACIÓN DEL ESTADO ***
    //                             sleep(2); // Esperar 2 segundos para que el estado se actualice en OpenAI

    //                             $updatedRun = OpenAI::threads()->runs()->retrieve($threadId, $run->id);
                                
    //                             // *** Registrar el last_error si existe ***
    //                             Log::info('Run status after tool output submission', ['run_id' => $run->id, 'status' => $updatedRun->status, 'last_error' => $updatedRun->lastError]);

    //                         } catch (Exception $e) {
    //                             Log::error('Failed to submit tool outputs to OpenAI', ['error_message' => $e->getMessage(), 'error_code' => $e->getCode(), 'tool_outputs_attempted' => $toolOutputs, 'thread_id' => $threadId, 'run_id' => $run->id]);
    //                             OpenAI::threads()->runs()->cancel($threadId, $run->id);
    //                             Log::info('Run cancelled after submission failure.', ['run_id' => $run->id]);
    //                         }
    //                     } else {
    //                         Log::warning('No tool outputs to submit for this run, possibly misconfigured or skipped.', ['run_id' => $run->id, 'thread_id' => $threadId]);
    //                     }

    //                     Log::info('Thread requires action END', ['run_id' => $run->id]);
    //                     break;
    //             }
    //         } 
    //     } while ($run->status != "completed");
        
    //     $runId = $run->id;
    //     // Log::info('Contenido de $run', [
    //     //     'run-id' => $run->id,
    //     //     'thread-id' => $run->threadId]);

    //     return $runId;
    // }

    
}
