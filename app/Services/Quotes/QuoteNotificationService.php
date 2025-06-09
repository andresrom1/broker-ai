<?php

namespace App\Services\Quotes;

use App\Models\QuoteRequest;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\AssistantFlow\ThreadManagerService;
use App\Services\Messages\MessageFormatterService;
//use App\Services\AssistantFlow\AssistantMessageSenderService;

class QuoteNotificationService
{
    protected ThreadManagerService $threadManager;
    protected MessageFormatterService $messageFormatter;
    //protected AssistantMessageSenderService $assistant;

    public function __construct(
        ThreadManagerService $threadManager,
        MessageFormatterService $messageFormatter,
        //AssistantMessageSenderService $assistant
    ) {
        $this->threadManager = $threadManager;
        $this->messageFormatter = $messageFormatter;
        //$this->sendMessage = $assistant;
    }

    /**
     * Envía las alternativas de cotización a través del chat de OpenAI al usuario.
     *
     * @param int $quoteRequestId El ID de la QuoteRequest que ha sido completada.
     * @return bool True si el mensaje fue enviado con éxito, false en caso contrario.
     */
    public function notifyUserWithAlternatives(int $quoteRequestId): bool
    {
        Log::info('Iniciando notificación de usuario con alternativas de cotización.', ['quote_request_id' => $quoteRequestId]);

        try {
            // 1. Recuperar la QuoteRequest y sus alternativas
            $quoteRequest = QuoteRequest::with('alternatives')->find($quoteRequestId);
            if (empty($quoteRequest->session_id)) {
                Log::error('Session ID no encontrado en QuoteRequest, no se puede obtener el thread para enviar mensaje.', ['quote_request_id' => $quoteRequestId]);
                return false;
            }
            if (!$quoteRequest) {
                Log::error('QuoteRequest no encontrada para enviar alternativas.', ['quote_request_id' => $quoteRequestId]);
                return false;
            }

            if ($quoteRequest->alternatives->isEmpty()) {
                Log::warning('No hay alternativas de cotización para enviar para esta QuoteRequest.', ['quote_request_id' => $quoteRequestId]);
                return false;
            }

            // 2. Construir el mensaje con las alternativas
            $messageContent = "¡Hola! Tu cotización ha sido completada. Aquí tienes las alternativas que hemos encontrado para ti:\n\n";

            foreach ($quoteRequest->alternatives as $index => $alternative) {
                $messageContent .= "**Opción " . ($index + 1) . ":**\n";
                $messageContent .= "- Compañía: " . $alternative->company . "\n";
                $messageContent .= "- Cobertura: " . $alternative->coverage . "\n";
                $messageContent .= "- Precio Mensual: $" . number_format($alternative->price, 0, ',', '.') . "\n";
                if (!empty($alternative->observations)) {
                    $messageContent .= "- Observaciones: " . $alternative->observations . "\n";
                }
                $messageContent .= "\n";
            }

            $messageContent .= "Si tenés alguna pregunta o querés proceder con alguna de estas opciones, no dudes en consultarnos.";
            $formattedMessage = $this->messageFormatter->formatForWeb($messageContent);

            // 3. Enviar el mensaje al servicio de envio
            
            //$this->assistant->sendMessage($quoteRequest->session_id, $formattedMessage);

            Log::info('Notificación de alternativas enviada al usuario con éxito.', ['quote_request_id' => $quoteRequestId]);
            return true;

        } catch (\Exception $e) {
            Log::error('Error al enviar alternativas de cotización al usuario.', [
                'quote_request_id' => $quoteRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
