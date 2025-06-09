<?php

namespace App\Services\Messages;

use Illuminate\Support\Facades\Log;

class MessageFormatterService
{
    /**
     * Prepara el texto de un mensaje para ser mostrado en una interfaz web.
     * Convierte los saltos de línea (\n) a etiquetas HTML <br>.
     *
     * @param string $message El mensaje de texto a formatear.
     * @return string El mensaje formateado con saltos de línea HTML.
     */
    public function formatForWeb(string $message): string
    {
        // Esta función asume que los saltos de línea ya son caracteres \n reales.
        //return nl2br($message);

        return $message;
    }

    /**
     * Desescapa los saltos de línea literales (ej. \\n) y luego formatea el texto
     * para ser mostrado en una interfaz web, convirtiendo \n a <br>.
     * Este método es útil cuando el texto de entrada ya viene con los \n escapados.
     *
     * @param string $message El mensaje de texto a desescapar y formatear.
     * @return string El mensaje desescapado y formateado con saltos de línea HTML.
     */
    public function unescapeNewlinesAndFormatForWeb(string $message): string
    {
        // Log::info('Mensaje del asistente:', ['Contenido:' => $message]);
        // // Primero, reemplaza la secuencia literal '\n' (barra invertida + 'n')
        // // con un carácter de salto de línea real.
        // $unescapedMessage = str_replace('\n', "\n", $message);
        
        // // Luego, aplica nl2br para convertir esos saltos de línea reales a <br>
        // return nl2br($unescapedMessage);

        return $message;
    }
}