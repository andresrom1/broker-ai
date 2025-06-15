<?php

namespace App\Services\AssistantFlow;

use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;

class ThreadManagerService
{
    /**
     * Obtiene un Thread ID existente o crea uno nuevo para una sesión.
     *
     * @param string $sessionId
     * @return string Thread ID
     * @throws \Exception Si hay un error al interactuar con OpenAI.
     */
    public function getOrCreateThread(string $sessionId): string
    {
        $cacheKey = "session_{$sessionId}";
        $threadId = Cache::get($cacheKey);

        if ($threadId) {
            try {
                // Verificar si el thread aún existe en OpenAI
                OpenAI::threads()->retrieve($threadId);
                Log::info('Existing thread retrieved from cache and verified with OpenAI', ['thread_id' => $threadId]);
                return $threadId;
            } catch (\Throwable $e) {
                // Si el thread ya no existe en OpenAI (ej. fue eliminado), crear uno nuevo.
                Log::warning('Cached thread not found in OpenAI, creating new one.', ['thread_id' => $threadId, 'error' => $e->getMessage()]);
                // La caché se invalidará al crear y guardar el nuevo Thread ID.
            }
        }

        // Si no hay thread en caché o el cacheado no es válido, crear uno nuevo
        try {
            $thread = OpenAI::threads()->create([]);
            $newThreadId = $thread->id;
            Cache::put($cacheKey, $newThreadId, now()->addDays(7));
            Log::info('New thread created and cached', ['thread_id' => $newThreadId]);
            return $newThreadId;
        } catch (\Exception $e) {
            Log::error('Failed to create new thread', ['error' => $e->getMessage()]);
            throw new \Exception('No se pudo crear el hilo de conversación con el asistente.');
        }
    }
    /**
     * Obtiene un Thread ID existente
     *
     * @param string $threadId
     * @return string Thread ID
     * @throws \Exception Si hay un error al interactuar con OpenAI.
     */
    public function getThread(string $threadId): string
    {
        //$threadId = Cache::get("session_{$sessionId}");
        
        Log::info(__METHOD__ . __LINE__, ['threadId' => $threadId]);
        
        try {
            // Verificar si el thread aún existe en OpenAI
            OpenAI::threads()->retrieve($threadId);
            Log::info('Existing thread retrieved from cache and verified with OpenAI', ['thread_id' => $threadId]);
            return $threadId;
        } catch (\Throwable $e) {
            // Si el thread ya no existe en OpenAI (ej. fue eliminado), lanzar una excepcion.
            Log::warning('Cached thread not found in OpenAI, Lanzando excepcion.', ['thread_id' => $threadId, 'error' => $e->getMessage()]);
            Log::error('Fallo al enviar el mensaje del admin al asistente');
            // La caché se invalidará al crear y guardar el nuevo Thread ID.
            throw new \Exception('No se pudo encontrar el hilo.');
        }
    }
}