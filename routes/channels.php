<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to this channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// --- NUEVA DEFINICIÓN DE CANAL PARA LAS COTIZACIONES ---
// Este canal debe coincidir con el nombre que usas en QuotesAlternativesUpdated::broadcastOn().
// Se usa "Channel" aquí porque en tu evento QuotesAlternativesUpdated, estás retornando new Channel().
// Si tu evento fuera a usar PrivateChannel, necesitarías Broadcast::channel y una lógica de autorización más robusta.
Broadcast::channel('user-quotes.{sessionId}', function ($user, $sessionId) {
    // Si tu aplicación no tiene usuarios autenticados para este chat,
    // o si el session_id es suficiente para identificar el "dueño" del canal,
    // puedes simplemente retornar true.
    // Si necesitas verificar que el usuario autenticado es el dueño de este sessionId,
    // la lógica iría aquí (ej. comparar $user->id con algo asociado a $sessionId en tu DB).
    return true; // Permite que cualquier cliente con este sessionId se suscriba.
});