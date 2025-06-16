<?php

namespace App\Services\AssistantFlow;

use App\Models\Lead; // Necesitamos el modelo Lead para crear/obtener
use App\Models\Message; // Necesitamos el modelo Message para la re-hidratación
use App\Services\Threads\ThreadPersistenceService;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;

class ThreadManagerService
{
    protected const OPENAI_THREAD_CACHE_PREFIX = 'openai_thread_';
    protected const CACHE_TTL_HOURS = 24; // Mantener en caché por un tiempo para respuestas rápidas

    protected ThreadPersistenceService $threadPersistenceService;

    public function __construct(ThreadPersistenceService $threadPersistenceService)
    {
        $this->threadPersistenceService = $threadPersistenceService;
    }

     /* Obtiene el Lead para una sesión dada, o lo crea si no existe.
     * Luego, obtiene el thread ID de OpenAI asociado a ese Lead.
     * Si no existe, crea un nuevo thread en OpenAI, lo re-hidrata con el historial
     * de mensajes del Lead desde la DB, y lo guarda en el Lead en la DB y en caché.
     *
     * @param string $sessionId
     * @return string Thread ID
     * @throws \Exception Si hay un error al interactuar con OpenAI.
     */
    // public function getOrCreateThread(string $sessionId): string
    // {
    //     $cacheKey = "session_{$sessionId}";
    //     $threadId = Cache::get($cacheKey);

    //     if ($threadId) {
    //         try {
    //             // Verificar si el thread aún existe en OpenAI
    //             OpenAI::threads()->retrieve($threadId);
    //             Log::info('Existing thread retrieved from cache and verified with OpenAI', ['thread_id' => $threadId]);
    //             return $threadId;
    //         } catch (\Throwable $e) {
    //             // Si el thread ya no existe en OpenAI (ej. fue eliminado), crear uno nuevo.
    //             Log::warning('Cached thread not found in OpenAI, creating new one.', ['thread_id' => $threadId, 'error' => $e->getMessage()]);
    //             // La caché se invalidará al crear y guardar el nuevo Thread ID.
    //         }
    //     }

    //     // Si no hay thread en caché o el cacheado no es válido, crear uno nuevo
    //     try {
    //         $thread = OpenAI::threads()->create([]);
    //         $newThreadId = $thread->id;
    //         Cache::put($cacheKey, $newThreadId, now()->addDays(7));
    //         Log::info('New thread created and cached', ['thread_id' => $newThreadId]);
    //         return $newThreadId;
    //     } catch (\Exception $e) {
    //         Log::error('Failed to create new thread', ['error' => $e->getMessage()]);
    //         throw new \Exception('No se pudo crear el hilo de conversación con el asistente.');
    //     }
    // }


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


    public function getOrCreateThread(string $sessionId): string
    {
        // 1. Obtener o crear el Lead para este sessionId
        $lead = Lead::firstOrCreate(
            ['session_id' => $sessionId],
            ['status' => 'new'] // Asignar estado inicial si es nuevo
        );
        Log::info(__METHOD__.__LINE__.'Lead obtenido/creado para session ID.', ['session_id' => $sessionId, 'lead_id' => $lead->id, 'lead_status' => $lead->status]);

        $threadId = $lead->thread_id; // Intentar obtener el threadId directamente del Lead

        // 2. Si el threadId no está en el Lead (DB), intentar de la caché como fallback/optimización
        if (!$threadId) {
            $cachedThreadId = Cache::get(self::OPENAI_THREAD_CACHE_PREFIX . $sessionId);
            if ($cachedThreadId) {
                // Verificar si el thread realmente existe en OpenAI
                try {
                    OpenAI::threads()->retrieve($cachedThreadId);
                    $threadId = $cachedThreadId;
                    Log::info('Thread ID recuperado de caché (fallback).', ['session_id' => $sessionId, 'thread_id' => $threadId]);
                    // Si se recuperó de caché pero no estaba en DB, guardar en DB ahora
                    $this->threadPersistenceService->saveThreadIdForSessionId($sessionId, $threadId);
                    $lead->thread_id = $threadId; // Actualizar el objeto Lead en memoria
                } catch (\Exception $e) {
                    Log::warning('Thread ID en caché es inválido o no existe en OpenAI, se eliminará y se creará uno nuevo.', ['session_id' => $sessionId, 'cached_thread_id' => $cachedThreadId, 'error' => $e->getMessage()]);
                    Cache::forget(self::OPENAI_THREAD_CACHE_PREFIX . $sessionId);
                    $threadId = null; // Reiniciar para crear uno nuevo
                }
            }
        }

        // 3. Si el threadId aún no se encontró, crear uno nuevo en OpenAI y re-hidratar
        if (!$threadId) {
            try {
                Log::info(__METHOD__.__LINE__.'No se encontró Thread ID para Lead, creando uno nuevo en OpenAI.', ['session_id' => $sessionId, 'lead_id' => $lead->id]);
                $thread = OpenAI::threads()->create([]);
                $threadId = $thread->id; // OpenAI devuelve el ID con el prefijo 'thread_'
                Log::info('Nuevo Thread ID creado en OpenAI.', ['session_id' => $sessionId, 'lead_id' => $lead->id, 'new_thread_id' => $threadId]);

                // Re-hidratar el nuevo hilo con mensajes del historial del Lead
                //$this->rehydrateThread($lead, $threadId);

                // Guardar el nuevo threadId en la base de datos (modelo Lead)
                $this->threadPersistenceService->saveThreadIdForSessionId($sessionId, $threadId);
                $lead->thread_id = $threadId; // Asegurarse de que el objeto Lead en memoria también se actualice

                // Almacenar el nuevo threadId en caché (para acceso rápido dentro de la misma petición)
                Cache::put(self::OPENAI_THREAD_CACHE_PREFIX . $sessionId, $threadId, now()->addHours(self::CACHE_TTL_HOURS));
                Log::info('Nuevo Thread ID cacheado.', ['session_id' => $sessionId, 'thread_id' => $threadId]);

            } catch (\Exception $e) {
                Log::error('Error al crear o almacenar nuevo Thread ID para Lead.', ['session_id' => $sessionId, 'lead_id' => $lead->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw new \Exception('Error al inicializar el hilo de conversación con el asistente: ' . $e->getMessage());
            }
        } else {
            // Si el threadId fue recuperado (de DB o caché), asegurarse de que esté en caché para futuras consultas rápidas
            if (!Cache::has(self::OPENAI_THREAD_CACHE_PREFIX . $sessionId)) {
                Cache::put(self::OPENAI_THREAD_CACHE_PREFIX . $sessionId, $threadId, now()->addHours(self::CACHE_TTL_HOURS));
                Log::info('Thread ID existente añadido a la caché.', ['session_id' => $sessionId, 'thread_id' => $threadId]);
            }
        }

        return $threadId;
    }

     /**
     * Re-hidrata un hilo de OpenAI con el historial de mensajes de un Lead.
     *
     * @param Lead $lead El objeto Lead.
     * @param string $newThreadId El ID del nuevo hilo de OpenAI.
     * @return void
     */
    protected function rehydrateThread(Lead $lead, string $newThreadId): void
    {
        Log::info('Iniciando re-hidratación del hilo de OpenAI.', ['lead_id' => $lead->id, 'new_thread_id' => $newThreadId]);

        $leadMessages = $lead->messages()
                             ->orderBy('created_at', 'asc')
                             ->get();

        if ($leadMessages->isEmpty()) {
            Log::info('No hay mensajes en la DB para re-hidratar el hilo.', ['lead_id' => $lead->id]);
            return;
        }

        foreach ($leadMessages as $dbMessage) {
            try {
                OpenAI::threads()->messages()->create(
                    $newThreadId,
                    [
                        'role'    => $dbMessage->role,
                        'content' => $dbMessage->content,
                        // Puedes añadir metadata aquí si es útil para OpenAI
                        // 'metadata' => ['db_message_id' => $dbMessage->id],
                    ]
                );
                Log::debug('Mensaje re-hidratado al hilo de OpenAI.', [
                    'thread_id' => $newThreadId,
                    'message_id_db' => $dbMessage->id,
                    'role' => $dbMessage->role,
                    'content_preview' => substr($dbMessage->content, 0, 50) . '...'
                ]);
            } catch (\Exception $e) {
                Log::error('Error al re-hidratar mensaje en hilo de OpenAI.', [
                    'lead_id' => $lead->id,
                    'message_id_db' => $dbMessage->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continuar con los demás mensajes o lanzar excepción si es crítico
            }
        }
        Log::info('Re-hidratación del hilo completada.', ['lead_id' => $lead->id, 'new_thread_id' => $newThreadId, 'messages_count' => $leadMessages->count()]);
    }
}