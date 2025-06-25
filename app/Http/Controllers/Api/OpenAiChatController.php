<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Services\AssistantService;
use Illuminate\Support\Facades\Log;

class OpenAiChatController extends Controller
{
    protected AssistantService $assistant;
    protected Lead $leadModel;    // Inyección del modelo Lead
    protected Message $messageModel; // Inyección del modelo Message

    public function __construct(
        AssistantService $assistant,
        Lead $leadModel,      // Recibe la instancia del modelo Lead
        Message $messageModel // Recibe la instancia del modelo Message
    ) {
        $this->assistant = $assistant;
        $this->leadModel = $leadModel;
        $this->messageModel = $messageModel;
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
                $validated['message'],
                
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

    /**
     * Recupera los mensajes históricos para un session_id dado.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:255',
        ]);

        $sessionId = $validated['session_id'];
        Log::info('Solicitud de mensajes históricos recibida.', ['session_id' => $sessionId]);

        try {
            $lead = $this->leadModel->where('session_id', $sessionId)->first();

            if (!$lead) {
                Log::info('No se encontró Lead para el session ID, retornando mensajes vacíos.', ['session_id' => $sessionId]);
                return response()->json(['messages' => []]);
            }

            // Recuperar todos los mensajes asociados a este Lead, ordenados por fecha de creación
            $messages = $this->messageModel->where('lead_id', $lead->id)
                                          ->orderBy('created_at', 'asc')
                                          ->get(['role', 'content','meta_data']) // Seleccionar solo las columnas necesarias
                                          ->toArray();

            Log::info('Mensajes históricos recuperados.', ['session_id' => $sessionId, 'Mensajes' => $messages]);

            return response()->json(['messages' => $messages]);

        } catch (\Exception $e) {
            Log::error('Error al recuperar mensajes históricos en ChatController.', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Error al cargar mensajes históricos.'], 500);
        }
    }
}