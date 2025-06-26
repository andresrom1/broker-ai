<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuoteRequestController;
use App\Http\Controllers\Quotes\QuoteAlternativeController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OpenAiChatController;
use App\Http\Controllers\Api\PushSubscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí registramos las rutas de la API. Se cargan con el middleware `api`.
|
*/

//Route::post('/quote-requests',    [QuoteRequestController::class, 'store']);
Route::get('/quote-requests/{dni}', [QuoteRequestController::class, 'showByDni']);
Route::post('/quote-alternatives', [QuoteAlternativeController::class, 'store']);

Route::post('/chat/send', [OpenAiChatController::class, 'send']);
Route::get('/messages', [OpenAiChatController::class, 'getMessages']);

Route::post('/subscribe', [PushSubscriptionController::class, 'store']);
Route::post('/unsubscribe', [PushSubscriptionController::class, 'destroy']); // Para futuras desuscripciones