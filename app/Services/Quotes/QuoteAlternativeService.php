<?php

namespace App\Services\Quotes;

use App\Models\QuoteRequest;
use App\Models\QuoteAlternative;
use App\Notifications\NewQuote;
use App\Services\AssistantFlow\AdminMessageForAssistantService;
use App\Services\AssistantFlow\RetrieveMessageService;
use App\Services\Messages\StoreMessageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\QuotesAlternativesUpdated; // Importa tu evento
use App\Services\Messages\MessageFormatterService;
use Illuminate\Support\Facades\Storage; // Importar la Facade Storage
use App\Models\QuoteAlternativeAttachment;

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

            $quoteRequest = QuoteRequest::with('alternatives.attachments')->findOrFail($quoteRequestId);
            
            // Opcional: Eliminar alternativas existentes si siempre se sobrescriben
            $quoteRequest->alternatives()->delete(); 

            $allAttachments = []; // Para recolectar todos los adjuntos para el mensaje final
            
            // Guardar las nuevas alternativas
            foreach ($alternativesData as $alt) {
                // Separar los datos de la alternativa de los archivos
                $file = $alt['pdf_file'] ?? null;
                unset($alt['pdf_file']); // Eliminar el archivo del array de datos para la alternativa

                $alternative = QuoteAlternative::create([
                    'quote_request_id' => $quoteRequestId,
                    'company'          => $alt['company'],
                    'price'            => $alt['price'],
                    'coverage'         => $alt['coverage'],
                    'observations'     => $alt['observations'] ?? null,
                ]);

                // Procesar y guardar el archivo si existe
                if ($file) {
                    $attachment = $this->processAndStoreAttachment($alternative, $file);
                    if ($attachment) {
                        $allAttachments[] = [
                            'alternative_id' => $alternative->id,
                            'file_name' => $attachment->file_name,
                            'file_url' => $attachment->file_url,
                        ];
                        Log::info(__METHOD__.__LINE__.'Archivo adjunto guardado para alternativa.', ['alternative_id' => $alternative->id, 'file_name' => $attachment->file_name]);
                    }
                }
            }
            
            // Marcar la solicitud como cotizada
            $this->quoteRequestService->updateStatus($quoteRequestId,true);
            
            DB::commit();
            Log::info(__METHOD__.__LINE__,['Status de quote actualizado']);

            // Disparar el evento *después* de que la transacción se ha completado
            // Asegúrate de recargar las alternativas si no lo hiciste con `with` al inicio
            // $quoteRequest->refresh(); // Recarga el modelo completo
            $quoteRequest->load('alternatives.attachments'); // Solo recarga las relaciones
            
            Log::info(__METHOD__.__LINE__,['$quoteRequest_' => $quoteRequest]);
            
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
                // 'attachments' => $alt->attachments->map(fn($att) => [ // Incluir adjuntos
                //         'file_name' => $att->file_name,
                //         'file_url' => $att->file_url,
                //     ])->toArray()                
            ])->toArray();

            $attachmentLinks = $quoteRequest->attachments
                    ->map(fn($att) => [ // Incluir adjuntos
                         'quote_alternative_id' =>$att->quote_alternative_id,
                         'file_name' => $att->file_name,
                         'file_url' => $att->file_url,
                     ])->toArray();
            
            // Log::info('Estos son los likns de los adjuntos',['Alternatives'=>$quoteRequest->attachments,'Contenido del array'=>$attachmentLinks]);
            Log::info('Estos son los likns de los adjuntos',['Contenido del array'=>$attachmentLinks]);
            
            $adminMessage = "Mensaje del Agente: " . $messageContent . json_encode($broadcastableAlternatives);
            $adminMessage .= !empty($attachmentLinks) ? "\nExisten archivos adjuntos, los cuales seran enviados al cliente en breve. No le preguntes al cliente que le parecen las alternativas. Esta pregunta se hara luego de enviar los links" : "\nNo hay archivos adjuntos para esta cotizacion";
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
            
            $metaData = ['link' => null ,
                        'type' => 'quote_alternative'];
            $this->storeMessage->store($quoteRequest->lead, 'assistant', $msg['message'], $openaiUserMessage, $metaData);
            
            
            // despachar el evento
            // --- LÍNEA DE DEPURACIÓN CRUCIAL ---
            Log::info('Intentando despachar el evento QuotesAlternativesUpdated.', ['quote_request_id' => $quoteRequestId]);
            \Event::dispatch(new QuotesAlternativesUpdated(
                $quoteRequest->id,
                $broadcastableAlternatives,
                $quoteRequest->lead->session_id, // Asegúrate de que este campo exista y esté poblado en QuoteRequest
                $msg['message'],
                $metaData
            ));

            // --- ¡NUEVO! Disparar la Notificación Push ---
            // Solo se envía si el Lead tiene suscripciones push activas.
            if ($quoteRequest->lead->pushSubscriptions->count() > 0) {
                Log::info('Enviando notificación push para Lead.', ['lead_id' => $quoteRequest->lead->id, 'subscriptions_count' => $quoteRequest->lead->pushSubscriptions->count()]);
                $quoteRequest->lead->notify(new NewQuote($quoteRequest, 'Ya esta lista tu cotizacion!'));
            } else {
                Log::info('No hay suscripciones push para este Lead, no se enviará notificación.', ['lead_id' => $quoteRequest->lead->id]);
            }
            // --- FIN NUEVO ---
           
            //Llama al servicio para agregar los mensajes con los links.
            // $adminMessage = "Mensaje del Agente: " . $messageContent . json_encode($broadcastableAlternatives);
            if (!empty($attachmentLinks)) {
                foreach ($attachmentLinks as $att) {
                    $metaData = ['link' => $att['file_url'],
                        'type' => 'quote_link'];
                    $adminMessage = "Mensaje del Agente: Este es el link de la cotizacion";
                    $adminMessage .= "\n".json_encode($att['file_url']);
                    $adminMessage .= "\nPor favor, formula una respuesta amigable para el usuario con estas opciones.";
                    $openaiUserMessage = $this->sendToAssistant->sendAdminMessageForAssistant( $threadId, $adminMessage, $quoteRequest->lead);
                    $msg = $this->retrieveMessageService->getLastMessage( $threadId);
                    $this->storeMessage->store($quoteRequest->lead, 'assistant', $msg['message'], $openaiUserMessage, $metaData);
                    \Event::dispatch(new QuotesAlternativesUpdated(
                        $quoteRequest->id,
                        $broadcastableAlternatives,
                        $quoteRequest->lead->session_id, // Asegúrate de que este campo exista y esté poblado en QuoteRequest
                        $msg['message'],
                        $metaData
                    ));
                }
            }

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

    /**
     * Procesa y almacena un archivo adjunto para una QuoteAlternative.
     *
     * @param QuoteAlternative $alternative La alternativa a la que se adjunta el archivo.
     * @param \Illuminate\Http\UploadedFile $file El archivo subido.
     * @return QuoteAlternativeAttachment|null El registro del adjunto creado, o null en caso de error.
     */
    protected function processAndStoreAttachment(QuoteAlternative $alternative, $file): ?QuoteAlternativeAttachment
    {
        try {
            // Define la ruta de almacenamiento dentro de 'storage/app/public/quote_attachments'
            $path = 'quote_attachments/' . $alternative->quoteRequest->id;
            
            // Guarda el archivo. Laravel generará un nombre de archivo único.
            $filePath = Storage::disk('public')->putFile($path, $file);

            // Genera la URL pública para acceder al archivo
            $fileUrl = Storage::url($filePath);
            Log::info(__METHOD__.__LINE__,['path' => $path, 'filePath'=>$filePath, 'fileUrl'=>$fileUrl]);

            return QuoteAlternativeAttachment::create([
                'quote_alternative_id' => $alternative->id,
                'file_name'            => $file->getClientOriginalName(), // Nombre original del archivo
                'file_path'            => $filePath,                       // Ruta interna
                'file_url'             => $fileUrl,                       // URL pública
                'mime_type'            => $file->getMimeType(),           // Tipo MIME
                'file_size'            => $file->getSize(),               // Tamaño en bytes
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar y almacenar adjunto.', [
                'alternative_id' => $alternative->id,
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
