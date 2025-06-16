<?php

namespace App\Services;

use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;

// Nuevas dependencias de servicios
use App\Services\AssistantFlow\ThreadManagerService;
use App\Services\AssistantFlow\RunManagerService;
use App\Services\AssistantFlow\ToolExecutionService;
use App\Services\Messages\MessageFormatterService;
use App\Models\Lead; // Importar el modelo Lead
use App\Models\Message; // Importar el modelo Message

class AssistantService
{
    protected string $assistantId = 'asst_8XHbWmWOrpTeqKKB23nsk3Hh';
    // ThreadRunnerService ya no es una dependencia directa aquí,
    // su lógica se moverá o se inyectará en ToolExecutionService.
    // protected ThreadRunnerService $threadRunner; 

    protected ThreadManagerService $threadManager;
    protected RunManagerService $runManager;
    protected ToolExecutionService $toolExecution;
    protected MessageFormatterService $messageFormatter;
    protected Lead $lead; // Inyectar el modelo Lead para buscar por session_id
    protected Message $message; // Inyectar el modelo Message para guardar mensajes


    public function __construct(
        ThreadManagerService $threadManager,
        RunManagerService $runManager,
        ToolExecutionService $toolExecution,
        MessageFormatterService $messageFormatter,
        Lead $lead, // Nueva inyección
        Message $message // Nueva inyección
    ) {
        $this->threadManager = $threadManager;
        $this->runManager = $runManager;
        $this->toolExecution = $toolExecution;
        $this->messageFormatter = $messageFormatter;
        $this->lead = $lead;
        $this->message = $message;
    }

    /**
     * Envía un mensaje al Assistant de OpenAI usando threads,
     * maneja function calls y retorna datos estructurados.
     *
     * @param string $sessionId
     * @param string $message
     * @return array ['reply' => string, 'function_call' => ?string, 'function_args' => ?array]
     */
    public function sendMessage(string $sessionId, string $message): array
    {
        Log::info(__METHOD__.__LINE__);
        
        // 1. Gestionar Thread (delegado a ThreadManagerService)
        $threadId = $this->threadManager->getOrCreateThread($sessionId);
        Log::info(__METHOD__.__LINE__.'Thread ID obtained', ['thread_id' => $threadId]);
        
        // Obtener el Lead asociado al sessionId para guardar los mensajes
        $lead = $this->lead->where('session_id', $sessionId)->first();
        if (!$lead) {
            Log::error(__METHOD__.__LINE__.'Lead no encontrado para sessionId en AssistantService.', ['session_id' => $sessionId]);
            return ['reply' => 'Lo siento, no pude encontrar tu sesión para continuar el chat.', 'function_call' => null, 'function_args' => null, 'run_id' => null];
        }

        // 2. Enviar mensaje del usuario al thread
        $openaiUserMessage = OpenAI::threads()->messages()->create($threadId, [
            'role'    => 'user',
            'content' => $message,
        ]);
        Log::info(__METHOD__.__LINE__.'User message added to thread', ['thread_id' => $threadId, 'message' => $message]);

        // 2.1. Guardar mensaje del usuario en la base de datos
        $this->message->create([
            'lead_id' => $lead->id,
            'role' => 'user',
            'content' => $message,
            'openai_message_id' => $openaiUserMessage->id, // Guardar el ID de OpenAI para referencia
        ]);
        Log::info(__METHOD__.__LINE__.'User message saved to database', ['lead_id' => $lead->id, 'message_id' => $openaiUserMessage->id]);


        // 3. Crear y ejecutar el Run, incluyendo el polling (delegado a RunManagerService)
        // RunManagerService se encargará de delegar tool_calls a ToolExecutionService.
        $run = $this->runManager->createAndPollRun($threadId, $this->assistantId, $lead->id);
        Log::info('Run processing completed', ['run_id' => $run->id, 'final_status' => $run->status]);

        $finalResponse = [
            'message' => '',
            'function_call' => null,
            'function_args' => null,
            'run_id' => $run->id
        ];

        // 4. Una vez que el Run está 'completed', recuperamos y procesamos los mensajes.
        if ($run->status === 'completed') {
            Log::info('Run completed successfully. Retrieving messages.', ['run_id' => $run->id]);
            $messages = OpenAI::threads()->messages()->list($threadId, ['order' => 'desc', 'limit' => 10])->data; // Limitar a los últimos 10 mensajes
            Log::info('messages received from thread', ['count' => count($messages), 'latest_messages_ids' => array_map(fn($msg) => $msg->id, array_slice($messages, 0, 5))]);
            
            // 5. Encontrar la última respuesta del assistant con contenido
            foreach ($messages as $msg) {
                if ($msg->role === 'assistant' && !empty($msg->content[0]->text->value ?? '')) {

                    // Verificar si el mensaje de OpenAI ya existe en nuestra DB para este lead y con el mismo ID de OpenAI
                    $existingMessage = $this->message->where('lead_id', $lead->id) //no confundir con $message(s)
                        ->where('openai_message_id', $msg->id)
                        ->first();

                    if (!$existingMessage) {
                        $this->message->create([
                            'lead_id' => $lead->id,
                            'role' => 'assistant',
                            'content' => $msg->content[0]->text->value,
                            'openai_message_id' => $msg->id,
                        ]);
                        Log::info(__METHOD__.__LINE__.'Assistant message saved to database', ['lead_id' => $lead->id, 'message_id' => $msg->id]);
                    } else {
                        Log::debug(__METHOD__.__LINE__.'Assistant message already exists in DB, skipping save.', ['lead_id' => $lead->id, 'message_id' => $msg->id]);
                    }

                    $finalResponse['message'] = $this->messageFormatter->formatForWeb($msg->content[0]->text->value);
                    return $finalResponse; // Retorna la primera respuesta del asistente encontrada
                }
            }
            Log::warning('No assistant message found in completed run.', ['run_id' => $run->id]);
            $finalResponse['reply'] = 'Lo siento, el asistente procesó tu solicitud pero no generó una respuesta de texto. Intentá de nuevo.';

        } else {
            // El Run no completó exitosamente (failed, cancelled, expired, etc.)
            Log::error('Run did not complete successfully', ['run_id' => $run->id, 'final_status' => $run->status, 'last_error' => $run->lastError]);
            $finalResponse['reply'] = 'Lo siento, hubo un error inesperado con el asistente. Por favor, intentá de nuevo.';
            // Puedes añadir más lógica aquí para manejar diferentes errores de estado
        }

        return $finalResponse;
    }
}
