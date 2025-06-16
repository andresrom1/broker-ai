<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AssistantService;
use Illuminate\Support\Facades\Log;

class OpenAiChatController extends Controller
{
    protected AssistantService $assistant;

    public function __construct(AssistantService $assistant)
    {
        $this->assistant = $assistant;
    }

    /**
     * Endpoint para enviar mensajes al Assistant de OpenAI
     * y obtener la respuesta generada.
     */
    public function send(Request $request)
    {
        Log::info('Entrada al ChatController::sendMessage.', ['request_all' => $request->all()]);
        $validated = $request->validate([
            'session_id' => 'required|string',
            'message'    => 'required|string',
        ]);

        try {
            $reply = $this->assistant->sendMessage(
                $validated['session_id'], 
                $validated['message']
            );

            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Error al comunicarse con el asistente',
                'details' => $e->getMessage(),
                'Archivo: ' => $e->getFile(),
                'Linea: '=> $e->getLine(),
            ], 500);
        }
    }
}