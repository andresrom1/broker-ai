<?php

namespace App\Services\Threads;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Exception;

class SubmitOutputStreamed {

    public function submitOutputStreamed($threadId, $runId , $toolCallId, $result) {
             
        try {
            Log::info('Ingreso a la funcion submit output con un solo callId');
            $output = OpenAI::threads()->runs()->submitToolOutputsStreamed(
                threadId: $threadId,
                runId: $runId,
                parameters: [
                    'tool_outputs' => [
                        [
                            'tool_call_id' => $toolCallId,
                            'output' => $result,
                        ],
                    ],
                ]);
            
            Log::info('Output', ['Output:' => $output]);
            return $output;
        } catch (Exception $e) {
        // Cualquier otro error
            Log::error('Error inesperado en submitOutput', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'thread_id' => $threadId ?? 'null',
                'run_id' => $runId ?? 'null'
            ]);
            Log::error('Missing required tool call IDs or outputs',);
            
            try {
                OpenAI::threads()->runs()->cancel($threadId,$runId);
            } catch (\Throwable $th) {
                Log::info('Error al cancelar el run');
            }
        }
        try {
            Log::info('Ingreso a la funcion submit output con dos callId');
            $output = OpenAI::threads()->runs()->submitToolOutputsStreamed(
                threadId: $threadId,
                runId: $runId,
                parameters: [
                    'tool_outputs' => [
                        [
                            'tool_call_id' => $toolCallId[0],
                            'output' => $result,
                        ],
                        [
                            'tool_call_id' => $toolCallId[1],
                            'output' => $result,
                        ],
                    ],
                ]
            );
            Log::info('Output', ['Output:' => $output]);
            return $output;
        } catch (Exception $e) {
        // Cualquier otro error
            Log::error('Error inesperado en submitOutput', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'thread_id' => $threadId ?? 'null',
                'run_id' => $runId ?? 'null'
            ]);
            Log::error('Missing required tool call IDs or outputs',);
            
            try {
                OpenAI::threads()->runs()->cancel($threadId,$runId);
            } catch (\Throwable $th) {
                Log::info('Error al cancelar el run.');
            }
        }
    }
} 

