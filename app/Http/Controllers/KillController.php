<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \OpenAI\Laravel\Facades\OpenAI;

class KillController extends Controller
{
    protected $client;
    protected string $assistantId = 'asst_8XHbWmWOrpTeqKKB23nsk3Hh';

    public function __construct()
    {
        //$this->client = OpenAI::client(config('services.openai.key'));
    }

    public function show () {
        return view('kill');
    }

    public function handle(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|string',
            'run_id' => 'required|string'
        ]);

        try {
            //1. Cancelar el Run
            // $runResponse = OpenAI::threads()->runs()->cancel(
            //     $request->thread_id,
            //     $request->run_id
            // );

            // 2. Eliminar el Thread
            $threadResponse = OpenAI::threads()->delete($request->thread_id);

            return back()->with([
                'success' => true,
                'message' => 'Run y thread cancelados exitosamente: ' . 
                             json_encode([
                                 //'run_status' => $runResponse->status,
                                 'thread_deleted' => $threadResponse->deleted
                             ])
            ]);

        } catch (\OpenAI\Exceptions\ErrorException $e) {
            $errorMessage = match ($e->getCode()) {
                404 => 'Recurso no encontrado (thread o run inválido)',
                409 => 'El run ya está en estado terminal (completed/cancelled/failed)',
                default => $e->getMessage()
            };

            return back()->with([
                'success' => false,
                'message' => "Error: $errorMessage"
            ]);
        }
    }
}

    

