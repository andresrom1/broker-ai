<?php
// app/Http/Controllers/Api/QuoteRequestController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuoteRequest;
use App\Services\TelegramNotifier;

class QuoteRequestController extends Controller {
    public function store(Request $request, TelegramNotifier $notifier) {
        $data = $request->validate([
            'vehicle_brand'      => 'required|string',
            'vehicle_model'      => 'required|string',
            'vehicle_version'    => 'nullable|string',
            'vehicle_fuel'       => 'nullable|string',
            'vehicle_year'       => 'required|integer|min:1900',
            'vehicle_postal_code'=> 'required|string',
            'dni'                => 'required|string',
            'coverage_type'      => 'required|string',
            'phone'              => 'required|string',
            'session_id'         => 'nullable|string',
        ]);

        $quoteRequest = QuoteRequest::create($data);

        // Notificar por Telegram
        $notifier->notifyNewQuoteRequest($quoteRequest);

        return response()->json(['message' => 'Quote request created'], 201);
    }

    public function showByDni($dni) {
        $quote = QuoteRequest::where('dni', $dni)
            ->where('quoted', true)
            ->with('alternatives')
            ->latest()
            ->first();

        if (!$quote) {
            return response()->json(['ready' => false], 200);
        }

        return response()->json([
            'ready' => true,
            'alternatives' => $quote->alternatives->map(fn($alt) => [
                'company' => $alt->company,
                'price' => $alt->price,
                'coverage' => $alt->coverage,
                'observations' => $alt->observations,
            ]),
        ], 200);
    }
}
