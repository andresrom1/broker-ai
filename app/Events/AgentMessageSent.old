<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sessionId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $message, string $sessionId)
    {
        $this->message = $message;
        $this->sessionId = $sessionId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
public function broadcastOn(): array
    {
        // Asegúrate que el tipo de canal (Channel/PrivateChannel) coincide con lo que usa Echo en el frontend
        // Si en el frontend usas Echo.channel(...), debe ser new Channel(...)
        // Si en el frontend usas Echo.private(...), debe ser new PrivateChannel(...)
        // Según tu código, usas Echo.channel, así que debe ser new Channel.
        return [
            new Channel('chat.session.' . $this->sessionId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message-added'; // Nombre del evento que escuchará Vue
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'from' => 'bot', // O 'assistant', según lo que tu Vue espere
            'text' => $this->message,
            'sessionId' => $this->sessionId,
        ];
    }
}

//**Nota:** Es crucial usar un canal privado (`Channel`) para la sesión del usuario (`chat.session.` + `sessionId`) para que los mensajes solo lleguen al usuario correcto. Esto requiere autenticación de canales de broadcasting en Laravel.
