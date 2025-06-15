<?php

namespace App\Services\AssistantFlow;

use Illuminate\Support\Facades\Log;

use OpenAI\Laravel\Facades\OpenAI;
use App\Services\Messages\MessageFormatterService;

class RetrieveMessageService {
    private MessageFormatterService $messageFormatter;

    public function __construct(MessageFormatterService $messageFormatter) {
        $this->messageFormatter = $messageFormatter;
    }
    
    /**
     * Develve el ultimo mensaje del Thread
     * 
     * @param mixed $threadId El threadId de OpenAi
     * @return array
     */
    public function getLastMessage ($threadId) {
        
        $messages = OpenAI::threads()->messages()->list($threadId, ['order' => 'desc', 'limit' => 10])->data; // Limitar a los últimos 10 mensajes
        Log::info('messages received from thread', [
            'count' => count($messages), 
            //'latest_messages_ids' => array_map(fn($msg) => $msg->id, array_slice($messages, 0, 5)),
            'messages' => $messages]);
        
        foreach ($messages as $msg) {
            
            if ($msg->role === 'assistant' && !empty($msg->content[0]->text->value ?? '')) {
                
                try {
                    $finalResponse['message'] = $this->messageFormatter->formatForWeb($msg->content[0]->text->value);
                    Log::info('Ultimo mensaje del thread encontrado con exito en' . __METHOD__);
                    return $finalResponse; // Retorna la primera respuesta del asistente encontrada
                
                } catch (\Throwable $th) {
                    Log::info('Error al obtener el ultimo mensaje del asistente en' . __METHOD__);
                }
                
            } 
        }
        return ['message' => 'Ocurrió un error al obtener el mensaje del asistente'  . __CLASS__ . '->' . __METHOD__];    
        
    }
}
