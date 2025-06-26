<?php

namespace App\Services\Notifications;

use App\Models\Lead;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushSubscriptionService
{
    protected PushSubscription $pushSubscriptionModel;
    protected Lead $leadModel; // Aún se necesita para encontrar el Lead por session_id

    public function __construct(PushSubscription $pushSubscriptionModel, Lead $leadModel)
    {
        $this->pushSubscriptionModel = $pushSubscriptionModel;
        $this->leadModel = $leadModel;
    }

    /**
     * Guarda una nueva suscripción de notificaciones push o actualiza una existente para un Lead.
     *
     * @param string $sessionId El ID de sesión del Lead.
     * @param array $subscriptionData Datos de la suscripción (endpoint, p256dh, auth, content_encoding).
     * @return PushSubscription|null
     */
    public function subscribe(string $sessionId, array $subscriptionData): ?PushSubscription
    {
        Log::info('Intentando suscribir un dispositivo para notificaciones push.', ['session_id' => $sessionId, 'endpoint_preview' => substr($subscriptionData['endpoint'], 0, 50)]);

        $lead = $this->leadModel->where('session_id', $sessionId)->first();

        if (!$lead) {
            Log::warning('No se encontró Lead para la suscripción push.', ['session_id' => $sessionId]);
            return null;
        }

        try {
            // Mapear los nombres de las keys de la API Web Push a las columnas de la DB del paquete
            $publicKey = $subscriptionData['keys']['p256dh'] ?? null;
            $authToken = $subscriptionData['keys']['auth'] ?? null;
            $contentEncoding = $subscriptionData['contentEncoding'] ?? null; // 'aesgcm' es común

            // Buscar si ya existe una suscripción con este endpoint para evitar duplicados
            $subscription = $this->pushSubscriptionModel->firstOrCreate(
                [
                    'endpoint' => $subscriptionData['endpoint'],
                ],
                [
                    // 'lead_id'           => $lead->id, // Ya no se usa
                    'subscribable_type' => Lead::class, // Establecer el tipo morfo al modelo Lead
                    'subscribable_id'   => $lead->id,    // Establecer el ID morfo al ID del Lead
                    'public_key'        => $publicKey,
                    'auth_token'        => $authToken,
                    'content_encoding'  => $contentEncoding,
                ]
            );

            // Si ya existía, asegurarse de que los campos sean correctos
            if (!$subscription->wasRecentlyCreated) {
                // Solo actualizar si hay cambios relevantes
                if ($subscription->subscribable_type !== Lead::class ||
                    $subscription->subscribable_id !== $lead->id ||
                    $subscription->public_key !== $publicKey ||
                    $subscription->auth_token !== $authToken ||
                    $subscription->content_encoding !== $contentEncoding) {

                    $subscription->update([
                        'subscribable_type' => Lead::class,
                        'subscribable_id'   => $lead->id,
                        'public_key'        => $publicKey,
                        'auth_token'        => $authToken,
                        'content_encoding'  => $contentEncoding,
                    ]);
                    Log::info('Suscripción push existente actualizada para Lead (polimórfica).', ['session_id' => $sessionId, 'subscription_id' => $subscription->id]);
                } else {
                    Log::info('Suscripción push existente encontrada, sin cambios necesarios.', ['session_id' => $sessionId, 'subscription_id' => $subscription->id]);
                }
            } else {
                Log::info('Nueva suscripción push creada para Lead (polimórfica).', ['session_id' => $sessionId, 'subscription_id' => $subscription->id]);
            }
            
            return $subscription;

        } catch (\Exception $e) {
            Log::error('Error al guardar/actualizar suscripción push (polimórfica).', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Elimina una suscripción push de la base de datos.
     *
     * @param string $endpoint El endpoint de la suscripción a eliminar.
     * @return bool
     */
    public function unsubscribe(string $endpoint): bool
    {
        Log::info('Intentando desuscribir un dispositivo.', ['endpoint_preview' => substr($endpoint, 0, 50)]);
        try {
            $deleted = $this->pushSubscriptionModel->where('endpoint', $endpoint)->delete();
            if ($deleted) {
                Log::info('Suscripción push eliminada con éxito.', ['endpoint_preview' => substr($endpoint, 0, 50)]);
            } else {
                Log::warning('No se encontró suscripción push para eliminar.', ['endpoint_preview' => substr($endpoint, 0, 50)]);
            }
            return (bool) $deleted;
        } catch (\Exception $e) {
            Log::error('Error al eliminar suscripción push.', [
                'endpoint_preview' => substr($endpoint, 0, 50),
                'error'            => $e->getMessage(),
                'trace'            => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
