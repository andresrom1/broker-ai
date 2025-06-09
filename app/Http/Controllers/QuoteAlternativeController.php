<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use App\Models\QuoteAlternative;
use Illuminate\Support\Facades\Log;

class QuoteAlternativeController extends Controller {
    public function store(Request $request) {
        $data = $request->validate([
            'quote_request_id'         => 'required|exists:quote_requests,id',
            'alternatives'             => 'required|array|min:1|max:2',
            'alternatives.*.company'   => 'required|string',
            'alternatives.*.price'     => 'required|integer',
            'alternatives.*.coverage'  => 'required|string',
            'alternatives.*.observations' => 'nullable|string',
        ]);

        foreach ($data['alternatives'] as $alt) {
            QuoteAlternative::create([
                'quote_request_id' => $data['quote_request_id'],
                'company'          => $alt['company'],
                'price'            => $alt['price'],
                'coverage'         => $alt['coverage'],
                'observations'     => $alt['observations'] ?? null,
            ]);
        }

        // Marca la solicitud como cotizada
        $quoteRequest = QuoteRequest::find($data['quote_request_id']);
        $quoteRequest->update(['quoted' => true]);
        
        Log::info('Alternativas de cotización guardadas y solicitud marcada como cotizada.', ['quote_request_id' => $quoteRequest->id]);


        return response()->json(['message' => 'Alternatives saved'], 201);
    }
}
