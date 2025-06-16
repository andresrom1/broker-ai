<?php

namespace App\Services\Threads;

use App\Models\Lead; // Necesitamos el modelo Lead
use Illuminate\Support\Facades\Log;

class ThreadPersistenceService
{
    /**
     * Obtiene el thread ID de OpenAI asociado a un session ID desde la base de datos.
     * Busca el Lead por session ID y retorna su thread_id.
     *
     * @param string $sessionId El ID de la sesión del usuario.
     * @return string|null El thread ID si se encuentra, o null si no.
     */
    public function getThreadIdBySessionId(string $sessionId): ?string
    {
        try {
            $lead = Lead::where('session_id', $sessionId)->first();

            if ($lead && $lead->thread_id) {
                Log::info('Thread ID recuperado de la base de datos para Lead.', [
                    'session_id' => $sessionId,
                    'lead_id' => $lead->id,
                    'thread_id' => $lead->thread_id
                ]);
                return $lead->thread_id;
            }

            Log::info('Thread ID no encontrado en la base de datos para session ID.', ['session_id' => $sessionId]);
            return null;

        } catch (\Exception $e) {
            Log::error('Error al recuperar Thread ID de DB por session ID.', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Guarda o actualiza el thread ID de OpenAI asociado a un session ID en la base de datos.
     * Asume que el Lead ya existe para el session ID dado.
     *
     * @param string $sessionId El ID de la sesión del usuario.
     * @param string $threadId El thread ID de OpenAI a guardar.
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public function saveThreadIdForSessionId(string $sessionId, string $threadId): bool
    {
        try {
            // Buscamos el Lead por session_id
            $lead = Lead::where('session_id', $sessionId)->first();

            if ($lead) {
                // Si existe, actualizamos su thread_id
                $lead->update(['thread_id' => $threadId]);
                Log::info('Thread ID guardado/actualizado en la base de datos para Lead.', [
                    'lead_id' => $lead->id,
                    'session_id' => $sessionId,
                    'thread_id' => $threadId
                ]);
                return true;
            }

            // Si no existe un Lead para este session_id, no podemos guardar el thread_id aquí.
            // Esto es importante: el Lead debe crearse primero (ej. cuando el usuario inicia el chat por primera vez).
            Log::warning(__METHOD__.__LINE__.'No se encontró Lead para guardar/actualizar Thread ID.', [
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'note' => 'Asegúrate de que el Lead se cree antes de intentar vincular un thread_id.'
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Error al guardar Thread ID en DB para Lead.', [
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
