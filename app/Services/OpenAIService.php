<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;


class OpenAIService
{
    public static function sendChatMessage(string $message, ?string $sessionId = null): string
    {
        $client = OpenAI::client(config('services.openai.api_key'));

        $params = [
            // ID de tu Assistant
            'assistant'   => 'asst_8XHbWmWOrpTeqKKB23nsk3Hh',
            'model'       => 'gpt-4',
            'messages'    => [
                ['role' => 'system', 'content' => 'Eres un asistente de cotizaciones de seguros.'],
                ['role' => 'user',   'content' => $message],
            ],
            'temperature' => 0.7,
        ];

        if ($sessionId) {
            $params['user'] = $sessionId;
        }

        $chat = $client->chat()->create($params);
        return $chat['choices'][0]['message']['content'] ?? '';
    }
}
