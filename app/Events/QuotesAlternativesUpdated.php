<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QuotesAlternativesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public int $quoteRequestId;
    public array $alternatives;
    public string $sessionId;
    public string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(int $quoteRequestId, array $alternatives, string $sessionId, string $message)
    {
        $this->quoteRequestId = $quoteRequestId;
        $this->alternatives = $alternatives;
        $this->sessionId = $sessionId;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user-quotes.' . $this->sessionId),
        ];
    }

    /**
     * The event's broadcast name.
     * Este es el nombre del evento que el frontend escuchará.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'alternatives.updated';
    }

    /**
     * Get the data to broadcast.
     * Define qué datos se enviarán con el evento al frontend.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        Log::info('Estos son los datos que se broadcastean' , [
            'quote_request_id' => $this->quoteRequestId,
            'alternatives' => $this->alternatives,
            'session_id' => $this->sessionId,
            'message' => $this->message,
        ]);
        return [
            'quote_request_id' => $this->quoteRequestId,
            'alternatives' => $this->alternatives,
            'session_id' => $this->sessionId,
            'message' => $this->message,
        ];}
}
