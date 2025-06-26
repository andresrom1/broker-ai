<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\Notifications\PushSubscriptionService; // Importar el servicio

class PushSubscriptionController extends Controller
{
    protected PushSubscriptionService $pushSubscriptionService;

    public function __construct(PushSubscriptionService $pushSubscriptionService)
    {
        $this->pushSubscriptionService = $pushSubscriptionService;
    }

    /**
     * Almacena una nueva suscripción de notificaciones push.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string',
            'subscription.expirationTime' => 'nullable', // Puede ser null
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        $sessionId = $request->input('session_id');
        $subscriptionData = $request->input('subscription');

        Log::info(__LINE__.'Recibida solicitud para almacenar suscripción push.', [
            'session_id' => $sessionId,
            'endpoint_preview' => substr($subscriptionData['endpoint'], 0, 50)
        ]);

        $subscription = $this->pushSubscriptionService->subscribe($sessionId, $subscriptionData);

        if ($subscription) {
            return response()->json(['message' => 'Suscripción guardada exitosamente.', 'subscription_id' => $subscription->id], 201);
        } else {
            return response()->json(['message' => 'Fallo al guardar la suscripción.', 'error' => 'Could not process subscription'], 500);
        }
    }

    /**
     * Elimina una suscripción de notificaciones push.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $endpoint = $request->input('endpoint');

        Log::info('Recibida solicitud para eliminar suscripción push.', ['endpoint_preview' => substr($endpoint, 0, 50)]);

        $deleted = $this->pushSubscriptionService->unsubscribe($endpoint);

        if ($deleted) {
            return response()->json(['message' => 'Suscripción eliminada exitosamente.'], 200);
        } else {
            return response()->json(['message' => 'No se pudo eliminar la suscripción o no se encontró.', 'error' => 'Subscription not found or could not be deleted'], 404);
        }
    }
}
