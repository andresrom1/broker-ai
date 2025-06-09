<?php

namespace App\Services;

use Log;
use Illuminate\Support\Facades\Http; // Asegúrate de que esta Facade esté importada


class TelegramNotifierService
{
    protected string $botToken;
    protected string $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    /**
     * Envía un mensaje a Telegram.
     *
     * @param array $data Datos del vehículo (finalVehicleData de la caché).
     * @param string $coverage Nombre de la cobertura (finalCoverageChosen de la caché).
     * @param int|null $quoteId El ID de la solicitud de cotización creada.
     * @return bool True si el mensaje fue enviado con éxito, false en caso contrario.
     */
    public function notify(array $data, string $coverage, ?int $quoteId): bool
    {
        Log::info('Iniciando envío de notificación de Telegram', [
            'vehicle_data' => $data, 
            'coverage_name' => $coverage, 
            'quote_id_received' => $quoteId
        ]);

        try {
            $quoteUrl = url("/admin/quotes/" . $quoteId); 
            
            // --- PREPARACIÓN DEL MENSAJE PARA MARKDOWN ---
            // Si el modo de parseo es 'Markdown' (no 'MarkdownV2'),
            // los caracteres como '_' dentro de palabras pueden causar problemas.
            // También, los guiones en números y otros símbolos pueden necesitar escape si no son parte de enlaces o texto formateado.
            // Para simplicidad, se formatea la cobertura.
            $formattedCoverage = str_replace('_', ' ', ucfirst($coverage)); // Convierte 'cobertura_a' a 'Cobertura a'

            // Construir el mensaje. Aquí se utiliza Markdown básico.
            // Para URLs en Markdown, se recomienda usar el formato [Texto del enlace](URL).
            $message = "🆕 *Nueva solicitud de cotización (ID: {$quoteId}):*\n\n" // Título en negrita
                . "*Datos del Vehículo:*\n" // Subtítulo en negrita
                . "  - Marca: {$data['marca']}\n"
                . "  - Modelo: {$data['modelo']}\n"
                . "  - Año: {$data['year']}\n"
                . "  - Versión: {$data['version']}\n"
                . "  - CP: {$data['cp']}\n"
                . "  - GNC: " . ($data['gnc'] ? 'Sí' : 'No') . "\n"
                . "*Cobertura Elegida:* {$formattedCoverage}\n\n" // Cobertura formateada
                . "Podés ver los detalles aquí: [Ver Solicitud]({$quoteUrl})"; // Enlace formateado en Markdown

            Log::info('Mensaje Telegram a enviar', ['message_text' => $message]);

            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id'    => $this->chatId,
                'text'       => $message,
                'parse_mode' => 'Markdown', // Mantiene el modo de parseo actual
            ]);

            // Telegram API devuelve un objeto JSON con la propiedad 'ok' (true/false)
            $isOk = $response->json('ok'); 
            
            if ($isOk) {
                Log::info('Mensaje de Telegram enviado con éxito.', ['quote_id' => $quoteId, 'telegram_response' => $response->json()]);
                return true;
            } else {
                Log::error('Error al enviar mensaje a Telegram (API Response).', [
                    'status_code' => $response->status(), // Código de estado HTTP
                    'response_body' => $response->body(), // Cuerpo de la respuesta de Telegram (útil para errores específicos)
                    'quote_id' => $quoteId
                ]);
                return false;
            }

        } catch (\Throwable $th) {
            Log::error('Excepción inesperada al enviar mensaje a Telegram.', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(), // Para depuración detallada
                'data_received' => $data,
                'coverage_received' => $coverage,
                'quote_id_received' => $quoteId
            ]);
            return false;
        }
    }
}