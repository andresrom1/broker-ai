<?php

namespace App\Services\Quotes;

use App\Models\QuoteRequest;
use App\Models\QuoteAlternative;
use App\Services\AssistantFlow\AdminMessageForAssistantService;
use App\Services\AssistantFlow\RetrieveMessageService;
use App\Services\Messages\StoreMessageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\QuotesAlternativesUpdated; // Importa tu evento
use App\Services\Messages\MessageFormatterService;


class QuoteAlternativeService
{
    private QuoteRequestService $quoteRequestService;
    private AdminMessageForAssistantService $sendToAssistant;
    private RetrieveMessageService $retrieveMessageService;
    private MessageFormatterService $messageFormatter;
    private StoreMessageService $storeMessage;

    public function __construct(
        QuoteRequestService $quoteRequestService, 
        AdminMessageForAssistantService $sendToAssistant,
        RetrieveMessageService $retrieveMessageService,
        MessageFormatterService $messageFormatter,
        StoreMessageService $storeMessage,){
            $this->quoteRequestService = $quoteRequestService;
            $this->sendToAssistant = $sendToAssistant;
            $this->retrieveMessageService = $retrieveMessageService;
            $this->messageFormatter = $messageFormatter;
            $this->storeMessage = $storeMessage;
    }

    /**
     * Actualiza las alternativas de cotización para una solicitud y la marca como cotizada,
     * disparando el evento de actualización.
     *
     * @param int $quoteRequestId El ID de la QuoteRequest.
     * @param array $alternativesData El array de alternativas a guardar.
     * @return array Resultado de la operación (ej. ['success' => bool, 'message' => string])
     */
    public function updateAlternativesAndCompleteQuote(int $quoteRequestId, array $alternativesData): array
    {
        Log::info('Ingreso al servicio de QuoteAlternativesService', ['$quoteRequestId' => $quoteRequestId]);
        try {
            DB::beginTransaction();

            $quoteRequest = QuoteRequest::with('alternatives')->findOrFail($quoteRequestId);
            
            // Opcional: Eliminar alternativas existentes si siempre se sobrescriben
            $quoteRequest->alternatives()->delete(); 
            
            // Guardar las nuevas alternativas
            foreach ($alternativesData as $alt) {
                QuoteAlternative::create([
                    'quote_request_id' => $quoteRequestId,
                    'company'          => $alt['company'],
                    'price'            => $alt['price'],
                    'coverage'         => $alt['coverage'],
                    'observations'     => $alt['observations'] ?? null,
                ]);
            }
            
            // Marcar la solicitud como cotizada
            $this->quoteRequestService->updateStatus($quoteRequestId,true);
            
            DB::commit();
            Log::info(__METHOD__.__LINE__,['Status de quote actualizado']);

            // Disparar el evento *después* de que la transacción se ha completado
            // Asegúrate de recargar las alternativas si no lo hiciste con `with` al inicio
            // $quoteRequest->refresh(); // Recarga el modelo completo
            $quoteRequest->load('alternatives'); // Luego carga las relaciones
            Log::info(__METHOD__,['$quoteRequest_' => $quoteRequest]);
            
            // Asegurarse de tener un Lead y un session_id válido
            if (!$quoteRequest->lead || empty($quoteRequest->lead->session_id)) {
                Log::error(__METHOD__.__LINE__.'Lead o Session ID no encontrado para QuoteRequest al intentar enviar alternativas.', [
                    'quote_request_id' => $quoteRequestId,
                    'lead_exists' => (bool) $quoteRequest->lead,
                    'session_id_empty' => empty($quoteRequest->lead->session_id ?? null)
                ]);
                return ['success' => false, 'message' => 'No se pudo notificar al usuario: Lead o Session ID ausente.'];
            }

            $sessionId = $quoteRequest->lead->session_id; // Obtener sessionId del Lead
            
          
            $messageContent = "¡Hola! Tu cotización ha sido completada. Aquí tienes las alternativas que hemos encontrado para ti:";
            
            // Mapear las alternativas a un array plano para el broadcast
            $broadcastableAlternatives = $quoteRequest->alternatives->map(fn($alt) => [
                'company' => $alt->company,
                'price' => $alt->price,
                'coverage' => $alt->coverage,
                'observations' => $alt->observations,
            ])->toArray();
            
            $adminMessage = "Mensaje del Agente: " . $messageContent . json_encode($broadcastableAlternatives);
            $adminMessage .= "\nPor favor, formula una respuesta amigable para el usuario con estas opciones.";
            
            // Aca tengo que usar el threadId que esta en la cache
            // con la clave '$quoteRequest->session_id' => threadId
            //$threadId = Cache::get('session_'.$quoteRequest->session_id);
            $threadId = $quoteRequest->lead->thread_id;

            Log::info(__METHOD__ . __LINE__ . ' Aqui se recupera el threadId para todo el flow de Agregar mensaje de Admin', 
            ['session_id' => $quoteRequest->lead->session_id,'$threadId' => $threadId]);

            // Llama a tu servicio existente para añadir el mensaje al hilo y disparar el Run
            Log::info( __METHOD__ . ": Este es el mensaje que se enviara al asistente:", ['Mensaje:' => $adminMessage]);
            $openaiUserMessage = $this->sendToAssistant->sendAdminMessageForAssistant( $threadId, $adminMessage, $quoteRequest->lead);
            
            //recuperar el ultimo mensaje del hilo
            $msg = $this->retrieveMessageService->getLastMessage( $threadId);
            Log::info(__METHOD__, [
                'Ultimo Mensaje' => $msg
            ]);
            
            $this->storeMessage->store($quoteRequest->lead, 'assistant', $msg['message'], $openaiUserMessage);

            // despachar el evento
            // --- LÍNEA DE DEPURACIÓN CRUCIAL ---
            Log::info('Intentando despachar el evento QuotesAlternativesUpdated.', ['quote_request_id' => $quoteRequestId]);

            \Event::dispatch(new QuotesAlternativesUpdated(
                $quoteRequest->id,
                $broadcastableAlternatives,
                $quoteRequest->lead->session_id, // Asegúrate de que este campo exista y esté poblado en QuoteRequest
                $msg['message']
            ));

            Log::info('Alternativas guardadas, quote completado y evento de actualización disparado.', ['quote_request_id' => $quoteRequestId]);
            
            return ['success' => true, 'message' => 'Alternativas guardadas y cotización completada.'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar alternativas y completar cotización: ' . $e->getMessage(), [
                'quote_request_id' => $quoteRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Error al guardar alternativas: ' . $e->getMessage()];
        }
    }
}
