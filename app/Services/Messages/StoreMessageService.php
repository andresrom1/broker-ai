<?php

namespace App\Services\Messages;

use Illuminate\Support\Facades\Log;
use App\Models\Message;

class StoreMessageService
{
    private Message $message; 
    public function __construct(
        Message $message) {
        $this->message = $message;
    }
    /**
     * Almacena el mensaje en la BD
     * @param mixed $lead El lead al cual pertenece el threadId
     * @param string $role
     * @param mixed $message El mensaje que se guarda
     * @param mixed $openaiUserMessage el msg_id de openai
     * @return bool
     */
    public function store ($lead, $role, $message, $openaiUserMessage = null) {
         // 2.1. Guardar mensaje del usuario en la base de datos
        Log::info(__METHOD__.__LINE__, [
            'Este debe ser el lead id' => $lead->id,
            'Este debe ser el role'=> $role,
            'Este debe ser el mensaje' => $message,
            'Este debe ser el id del mensaje de OpenAi' => $openaiUserMessage]);
        Log::info(__METHOD__.__LINE__, [
            'Este debe ser el mensaje' => $message]);
        $this->message->create([
            'lead_id' => $lead->id,
            'role' => $role,
            'content' => $message,
            'openai_message_id' => $openaiUserMessage->id, // Guardar el ID de OpenAI para referencia
        ]);
        Log::info(__METHOD__.__LINE__.'User message saved to database', ['lead_id' => $lead->id, 'message_id' => $openaiUserMessage->id]);
        return true;
    }
    
}