<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage; // Importar la clase de mensaje WebPush
use NotificationChannels\WebPush\WebPushChannel; // Importar el canal WebPush
use App\Models\QuoteRequest; // Importar el modelo QuoteRequest
use Illuminate\Support\Facades\Log; // Para logs

class NewQuote extends Notification
{
    use Queueable;

    protected QuoteRequest $quoteRequest;
    protected string $messageContent; // El mensaje principal (ej. "Tu cotización está lista")

    /**
     * Create a new notification instance.
     *
     * @param QuoteRequest $quoteRequest La solicitud de cotización completada.
     * @param string $messageContent El mensaje principal a mostrar al usuario.
     * @return void
     */
    public function __construct(QuoteRequest $quoteRequest, string $messageContent)
    {
        $this->quoteRequest = $quoteRequest;
        $this->messageContent = $messageContent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Solo enviar a través del canal WebPush
        return [WebPushChannel::class];
        //return ['mail'];
    }

        /**
     * Get the web push representation of the notification.
     *
     * @param mixed $notifiable (En este caso, será una instancia de App\Models\Lead)
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable): WebPushMessage
    {
        // Construye la URL a la que se redirigirá al hacer clic en la notificación.
        // Asegúrate de que esta URL sea accesible públicamente y dirija al lugar correcto en tu app.
        $url = url('/chat'); // Puedes hacerla más específica: url("/quotes/{$this->quoteRequest->id}")
        
        // Log para depuración
        Log::info('Preparando notificación WebPush.', [
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
            'quote_request_id' => $this->quoteRequest->id,
            'title' => '¡Cotización Lista!',
            'body' => $this->messageContent,
            'url' => $url
        ]);

        return (new WebPushMessage)
            ->title('¡Cotización Lista!') // Título de la notificación
            ->body($this->messageContent) // Cuerpo de la notificación
            ->action('Ver Cotización', 'view_quote') // Botón de acción (opcional)
            ->data(['id' => $this->quoteRequest->id, 'url' => $url]) // Datos adicionales para el Service Worker
            ->icon('/favicon.ico') // Icono de la notificación (opcional)
            ->tag('quote-request-' . $this->quoteRequest->id) // Un tag para agrupar notificaciones (opcional)
            ->badge('/favicon.ico') // Icono para el badge en Android (opcional)
            ->renotify(true) // Permite que la notificación suene de nuevo si hay una nueva (opcional)
            ->vibrate([100, 50, 100]); // Patrón de vibración (opcional)
   
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
