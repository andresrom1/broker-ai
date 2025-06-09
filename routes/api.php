<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuoteRequestController;
use App\Http\Controllers\QuoteAlternativeController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OpenAiChatController;

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
