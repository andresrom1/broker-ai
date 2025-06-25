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
     * @param mixed|null $openaiUserMessage el msg_id de openai
     * @param mixed $metaData La metadata incluye informacion relevante para el frontend, por ejemplo si el mensaje es un link
     * @return bool
     */
    public function store ($lead, $role, $message, $openaiUserMessage = null, $metaData = null) {
         // 2.1. Guardar mensaje del usuario en la base de datos
        Log::info(__METHOD__.__LINE__, [
            'Este debe ser el lead id' => $lead->id,
            'Este debe ser el role'=> $role,
            'Este debe ser el mensaje' => !empty($message),
            'Este debe ser el id del mensaje de OpenAi' => !empty($openaiUserMessage),
            'Meta Data' => $metaData]);
        $this->message->create([
            'lead_id' => $lead->id,
            'role' => $role,
            'content' => $message,
            'openai_message_id' => $openaiUserMessage->id, // Guardar el ID de OpenAI para referencia
            'meta_data' => $metaData,
        ]);
        Log::info(__METHOD__.__LINE__.'User message saved to database', ['lead_id' => $lead->id, 'message_id' => $openaiUserMessage->id]);
        return true;
    }
    
}