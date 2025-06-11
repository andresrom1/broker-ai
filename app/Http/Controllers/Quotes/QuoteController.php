<?php

namespace App\Http\Controllers\Quotes;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use App\Models\QuoteAlternative;
use App\Services\Quotes\QuoteNotificationService;
use Illuminate\Support\Facades\Log;

class QuoteController extends Controller {
    protected QuoteNotificationService $quoteNotificationService;

    public function __construct(QuoteNotificationService $quoteNotificationService)
    {
        $this->quoteNotificationService = $quoteNotificationService;
    }
    public function index() {
        $requests = QuoteRequest::get();
        return view('admin.quotes.index', compact('requests'));
    }

    public function show($id) {
        $request = QuoteRequest::with('alternatives')->findOrFail($id);
        return view('admin.quotes.show', compact('request'));
    }

    // public function storeAlternatives(Request $request, $id) {
        
    //     $data = $request->validate([
    //         'quote_request_id'         => 'required|exists:quote_requests,id',
    //         'alternatives'             => 'required|array|min:1|max:2',
    //         'alternatives.*.company'   => 'required|string',
    //         'alternatives.*.price'     => 'required|integer',
    //         'alternatives.*.coverage'  => 'required|string',
    //         'alternatives.*.observations' => 'nullable|string',
    //     ]);
        
    //     foreach ($data['alternatives'] as $alt) {
    //         QuoteAlternative::create([
    //             'quote_request_id' => $data['quote_request_id'],
    //             'company'          => $alt['company'],
    //             'price'            => $alt['price'],
    //             'coverage'         => $alt['coverage'],
    //             'observations'     => $alt['observations'] ?? null,
    //         ]);
    //     }
        
    //     // Marca la solicitud como cotizada
    //     $quoteRequest = QuoteRequest::find($data['quote_request_id']);
    //     $quoteRequest->update(['quoted' => true]);
    //     Log::info('Quote Reques encontrada:' , ['Datos' => $quoteRequest]);

    //     // --- NUEVA LÓGICA: Notificar al usuario con las alternativas ---
    //     $this->quoteNotificationService->notifyUserWithAlternatives($quoteRequest->id);
    //     Log::info('Notificación de alternativas de cotización disparada desde Admin.', ['quote_request_id' => $quoteRequest->id]);
        
    //     //return response()->json(['message' => 'Alternatives saved'], 201);
    //     return redirect()->route('admin.quotes.index')->with('success', 'Alternativas guardadas');
    // }
}