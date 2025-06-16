<?php
namespace App\Http\Controllers\Quotes;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Services\Quotes\QuoteAlternativeService;
use Illuminate\Http\Request;
use App\Models\QuoteAlternative;
use Illuminate\Support\Facades\Log;


class QuoteAlternativeController extends Controller {
    protected QuoteAlternativeService $quoteAlternativeService;

    public function __construct(QuoteAlternativeService $quoteAlternativeService) {
        $this->quoteAlternativeService = $quoteAlternativeService;
    }
    public function store(Request $request) {
        
        $data = $request->validate([
            'quote_request_id'         => 'required|exists:quote_requests,id',
            'alternatives'             => 'required|array|min:1|max:2',
            'alternatives.*.company'   => 'required|string',
            'alternatives.*.price'     => 'required|integer',
            'alternatives.*.coverage'  => 'required|string',
            'alternatives.*.observations' => 'nullable|string',
        ]);
        Log::info(__METHOD__.__LINE__, ['Request:' => $data]);
        
        // Delegar la lógica de guardar alternativas, completar la cotización y disparar el evento al nuevo servicio       
        $result = $this->quoteAlternativeService->updateAlternativesAndCompleteQuote( // <--- Llama al nuevo servicio
            $data['quote_request_id'],
            $data['alternatives']
        );

        if ($result['success']) {
            Log::info('Alternativas de cotización procesadas y evento disparado exitosamente.', ['quote_request_id' => $data['quote_request_id']]);
            return redirect(route('quotes.index'))->with('message',response()->json(['message' => $result['message']], 201));
            // return response()->json(['message' => $result['message']], 201);
        } else {
            Log::error('Fallo al procesar alternativas de cotización.', [
                'quote_request_id' => $data['quote_request_id'],
                'error_message' => $result['message'],
                'original_error' => $result['error'] ?? 'N/A', // Captura el error original si el servicio lo devuelve
            ]);
            return response()->json(['message' => $result['message']], 500); // 500 si hay un error en el servidor
        }
    }
}
