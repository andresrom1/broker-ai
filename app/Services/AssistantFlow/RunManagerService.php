<?php

namespace App\Services\AssistantFlow;

use Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;

// Dependencia para delegar la ejecución de herramientas
use App\Services\AssistantFlow\ToolExecutionService; // Mover a esta ruta

class RunManagerService
{
    protected int $maxPollingAttempts = 60; // Total de 120 segundos
    protected int $pollingDelay = 1; // Segundos entre cada consulta

    protected ToolExecutionService $toolExecutionService;

    public function __construct(ToolExecutionService $toolExecutionService)
    {
        $this->toolExecutionService = $toolExecutionService;
    }

    /**
     * Crea un nuevo Run y lo consulta hasta que finalice o requiera una acción.
     *
     * @param string $threadId El ThreadId de OpenAi
     * @param string $assistantId El AssistantId de OpenAi
     * @param int $leadId El id del Lead
     * @return ThreadRunResponse El objeto Run final (completed, failed, etc.)
     * @throws \Exception Si el Run falla o alcanza el timeout.
     */
    public function createAndPollRun(string $threadId, string $assistantId, $leadId): ThreadRunResponse
    {
        $run = OpenAI::threads()->runs()->create(
            $threadId,
            ['assistant_id' => $assistantId]
        );
        Log::info('Run initiated for polling', ['run_id' => $run->id, 'thread_id' => $threadId, 'status' => $run->status]);

        $currentAttempt = 0;
        do {
            Log::info('Polling Run status...', ['run_id' => $run->id, 'current_status' => $run->status, 'attempt' => $currentAttempt + 1]);
            sleep($this->pollingDelay); // Espera antes de consultar de nuevo

            try {
                $run = OpenAI::threads()->runs()->retrieve($threadId, $run->id); // Obtiene el estado más reciente del Run
            } catch (\Exception $e) {
                Log::error('Error retrieving Run status during polling', ['run_id' => $run->id, 'error' => $e->getMessage()]);
                throw new \Exception('Error al consultar estado del Run: ' . $e->getMessage());
            }

            $currentAttempt++;

            // Si el Run requiere una acción (tool call), la delegamos y esperamos que el Run reanude
            if ($run->status === 'requires_action') {
                Log::info('Run requires action, delegating to ToolExecutionService', ['run_id' => $run->id]);
                // El ToolExecutionService procesará las tool_calls y enviará los outputs.
                // Después de que submitToolOutputsStreamed se complete, el Run volverá a 'queued' o 'in_progress'.
                try {
                    $this->toolExecutionService->handleToolCalls($threadId, $run, $leadId);
                    Log::info('Tool calls handled by ToolExecutionService', ['run_id' => $run->id]);
                } catch (\Exception $e) {
                    Log::error('Error handling tool calls by ToolExecutionService', ['run_id' => $run->id, 'error' => $e->getMessage()]);
                    // Si el manejo de herramientas falla, el Run debe ser cancelado para no quedar atascado.
                    OpenAI::threads()->runs()->cancel($threadId, $run->id);
                    throw new \Exception('Fallo al procesar tool calls: ' . $e->getMessage());
                }
                // Después de manejar las tool calls, el Run debería estar en 'queued' o 'in_progress' de nuevo.
                // Continuamos el polling.
            }

            // Verificar si se alcanzó el límite de intentos (timeout)
            if ($currentAttempt >= $this->maxPollingAttempts) {
                Log::error('Polling timeout reached for Run', ['run_id' => $run->id, 'status' => $run->status]);
                // Intentar cancelar el Run para liberar recursos en OpenAI
                try {
                    OpenAI::threads()->runs()->cancel($threadId, $run->id);
                    Log::info('Run cancelled due to polling timeout.', ['run_id' => $run->id]);
                } catch (\Exception $cancelE) {
                    Log::error('Failed to cancel Run after polling timeout', ['run_id' => $run->id, 'error' => $cancelE->getMessage()]);
                }
                throw new \Exception('Timeout esperando respuesta del asistente.');
            }

        } while (!in_array($run->status, ['completed', 'failed', 'cancelled', 'expired'])); // Espera hasta un estado final

        // Retorna el Run final, ya sea completado o con error.
        return $run;
    }
}