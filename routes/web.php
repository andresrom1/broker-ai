<?php

use App\Http\Controllers\KillController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Quotes\QuoteController;
use App\Http\Controllers\Quotes\QuoteAlternativeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('quote-form');
});

Route::get('/dispatch', function () {
    // Reemplaza 'TU_SESSION_ID_DEL_FRONTEND' con el sessionId real de tu navegador/ChatWidget
    $sessionId = 'g8QfUQu4qze82aAtp9HyPbaM';

    $quoteRequestId = 1; // Puedes usar cualquier ID de quote request válido o uno para pruebas

    $alternatives = [
        [
            'company' => 'Aseguradora Alfa',
            'price' => 12500,
            'coverage' => 'cobertura_a',
            'observations' => 'Opción con cobertura básica ampliada.',
        ],
        [
            'company' => 'Seguros Beta',
            'price' => 18000,
            'coverage' => 'cobertura_b',
            'observations' => 'Opción con cobertura todo riesgo con franquicia.',
        ],
    ];

    $message = "¡Hola! Tu cotización ha sido completada por el agente. Aquí tienes las alternativas que hemos encontrado para ti:";

    \Event::dispatch(new \App\Events\QuotesAlternativesUpdated(
        $quoteRequestId,
        $alternatives,
        $sessionId,
        $message
    ));
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::view('/cotizar', 'quote-form')->middleware('auth');

Route::middleware('auth')->group(function(){
    Route::get('/admin/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/admin/quotes/{id}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/admin/quotes/{id}', [QuoteAlternativeController::class, 'store'])->name('quotes.alternatives.store');
});

Route::get('kill', [KillController::class,'show'])->name('kill.show');
Route::post('/kill/handle', [KillController::class, 'handle'])->name('kill.handle');


require __DIR__.'/auth.php';
