<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NotificationChannels\WebPush\PushSubscription as BasePushSubscription;

class PushSubscription extends BasePushSubscription
{
    use HasFactory;

    protected $fillable = [
        //'lead_id',              // Tu clave foránea personalizada
        'subscribable_type',    // Parte del morphs() del paquete
        'subscribable_id',      // Parte del morphs() del paquete
        'endpoint',
        'public_key',           // <-- Renombrado de p256dh
        'auth_token',           // <-- Renombrado de auth
        'content_encoding',     // Nueva columna del paquete
    ];

    /**
     * Define la relación polimórfica para el paquete WebPush.
     * Aunque usamos 'lead_id', el paquete también usa 'subscribable'.
     */
    // public function subscribable()
    // {
    //     return $this->morphTo();
    // }
}
