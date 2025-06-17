<?php

namespace App\Services\AssistantFlow; // Lo guardo en AssistantFlow porque es parte del flujo del assistant

use App\Services\Messages\MessageFormatterService;
use App\Services\Messages\StoreMessageService;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\AssistantFlow\ThreadManagerService;

class AdminMessageForAssistantService // <-- Nuevo nombre de la clase
{
    protected ThreadManagerService $threadManager;
    protected MessageFormatterService $messageFormatter;
    protected StoreMessageService $storeMessage;
    protected string $assistantId;

    public function __construct(
        ThreadManagerService $threadManager,
        MessageFormatterService $messageFormatter,
        StoreMessageService $storeMessage,
    ) {
        $this->storeMessage = $storeMessage;
        $this->threadManager = $threadManager;
        $this->messageFormatter = $messageFormatter;
        $this->assistantId = env('OPENAI_ASSISTANT_ID', 'asst_8XHbWmWOrpTeqKKB23nsk3Hh'); 
    }

    /**
     * Permite a un administrador enviar un mensaje a un hilo de usuario de OpenAI,
     * añadiendo contexto al Asistente y disparando un Run para su procesamiento.
     * El mensaje se añade con el rol 'assistant' para que el Asistente lo lea como parte de su propio contexto.
     *
     * @param string $threadId El threadId del hilo del assistant
     * @param string $adminMessageContent El contenido del mensaje del administrador en texto plano.
     * @param mixed $lead El lead al cual pertenece el threadId
     * @return mixed Devuelve el Message Object de OpenAi 
     */
    public function sendAdminMessageForAssistant($threadId, string $adminMessageContent, $lead): mixed // <-- Nuevo nombre del método
    {
        Log::info('Intentando enviar mensaje de admin para contexto del asistente.', ['threadId' => $threadId]);

        try {
            // 1. Obtener el thread ID para la sesión del usuario
            //$threadId = $this->threadManager->getOrCreateThread($sessionId);
            $threadId = $this->threadManager->getThread($threadId);
            Log::info('Thread ID obtenido para mensaje de admin.', ['thread_id' => $threadId]);

            // 2. Formatear el mensaje (ej. convertir \n a <br> si es necesario, aunque en este contexto es menos crítico)
            // Ya que es un mensaje para el asistente, no siempre querrás HTML, pero lo mantengo por consistencia.
            $formattedMessage = $this->messageFormatter->unescapeNewlinesAndFormatForWeb($adminMessageContent);
            
            // 3. Añadir el mensaje al thread con el rol 'assistant'.
            // Esto es crucial para que el Asistente de IA lo reconozca como contexto interno.
            $openaiUserMessage = OpenAI::threads()->messages()->create($threadId, [
                'role'    => 'assistant', // user | assistant El rol es 'assistant' para que el Asistente lo procese como contexto interno
                'content' => $formattedMessage,
            ]);
            Log::info('Mensaje de admin añadido al historial del hilo con rol "assistant".', ['thread_id' => $threadId, 'message_preview' => substr($formattedMessage, 0, 100) . '...']);

            // 4. Crear un nuevo Run para que OpenAI procese el mensaje recién añadido
            // y el Asistente pueda generar una respuesta basada en este nuevo contexto.
            $run = OpenAI::threads()->runs()->create(
                $threadId,
                ['assistant_id' => $this->assistantId]
            );
            Log::info('Run iniciado para procesar el mensaje de contexto del admin.', ['thread_id' => $threadId, 'assistant_id' => $this->assistantId]);
            Log::info(__METHOD__, ['Run status' => $run->status]);
            $attempt =0;
            // 5. Espera a que el run este completed
            do {
                ($attempt > 2) ?? sleep(1);

                $run = OpenAI::threads()->runs()->retrieve($threadId, $run->id); // Recupera el estado actual del Run
                //Log::info(__METHOD__, ['Run status' => $run->status]);
            } while (!in_array($run->status, ['completed', 'failed', 'cancelled', 'expired'])); // Espera hasta un estado final
            Log::info(__METHOD__. ' ' .__LINE__. 'El status del run es:', [ 'Ejecuta en:' => 'AdminMessageForAssistantService.php', 'Run Status' => $run->status]);

            //$this->storeMessage->store($lead,'assistant', $formattedMessage, $openaiUserMessage);

            return $openaiUserMessage;

        } catch (\Exception $e) {
            Log::error('Fallo al enviar mensaje de admin para contexto del asistente.', [
                'threadid' => $threadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
