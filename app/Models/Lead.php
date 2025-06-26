<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class Lead extends Model
{
    use HasFactory;
    use HasPushSubscriptions;
    use Notifiable;

    protected $fillable = [
        'session_id',
        'thread_id',
        'dni',
        'name',
        'email',
        'phone',
        'status',
    ];

    /**
     * Define la relación: un Lead puede tener muchas QuoteRequests.
     */
    public function quoteRequests()
    {
        return $this->hasMany(QuoteRequest::class);
    }

    /**
     * Define la relación: un Lead puede tener muchos Messages.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Define la relación polimórfica: un Lead puede tener muchas PushSubscriptions.
     * Esto es clave para que las notificaciones push sepan a qué suscripciones enviar.
     * El primer argumento es la clase del modelo de suscripción.
     * El segundo argumento es el nombre de la relación polimórfica definida en la migración ('subscribable').
     */
    public function pushSubscriptions()
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }
}

