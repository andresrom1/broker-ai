<?php

namespace App\Providers;

use App\Http\Controllers\Quotes\QuoteAlternativeController;
use App\Services\AssistantFlow\RetrieveMessageService;
use App\Services\AssistantFlow\ThreadManagerService;
use App\Services\Messages\MessageFormatterService;
use App\Services\Quotes\QuoteAlternativeService;
use App\Services\Quotes\QuoteRequestService;
use Illuminate\Support\ServiceProvider;
use App\Services\AssistantFlow\AdminMessageForAssistantService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // QuoteRequestService y QuoteAlternativeService ya no necesitan un binding 'singleton' explícito.
        // Laravel los instanciará automáticamente cuando sean requeridos.

        // Ejemplo si QuoteAlternativeService tuviera dependencias (ej. QuoteRequestService)
        // Aunque no es un singleton, aún necesitas decirle a Laravel cómo construirlo si sus dependencias no son autowireables fácilmente
        $this->app->bind(QuoteAlternativeService::class, function ($app) {
            return new QuoteAlternativeService(
                $app->make(QuoteRequestService::class), // Inyecta QuoteRequestService
                $app->make(AdminMessageForAssistantService::class),
                $app->make(RetrieveMessageService::class), // Inyecta QuoteRequestService
                $app->make(MessageFormatterService::class)
                // Si QuoteAlternativeService tuviera más dependencias, las inyectarías aquí también
                // $app->make(ThreadManagerService::class),
            );
        });

        // Para QuoteRequestService, si no tiene dependencias en su constructor,
        // Laravel lo autowireará sin necesidad de un binding explícito 'bind' o 'singleton'.
        // Si tuviera dependencias, lo harías así:
        // $this->app->bind(QuoteRequestService::class, function ($app) {
        //     return new QuoteRequestService(); // O con sus dependencias
        // });


        // Asegúrate de que el controlador pueda recibir la inyección.
        // Esto es un "binding contextual" y puede ser útil si hay ambigüedad o quieres ser explícito.
        // En muchos casos, Laravel puede autowirear esto sin este bloque 'when'.
        $this->app->when(QuoteAlternativeController::class)
            ->needs(QuoteAlternativeService::class)
            ->give(function ($app) {
                return $app->make(QuoteAlternativeService::class);
            });

         // --- CORRECCIÓN AQUÍ: Binding para RetrieveMessageService ---
        // Depende de MessageFormatterService
        $this->app->bind(RetrieveMessageService::class, function ($app) {
            return new RetrieveMessageService(
                $app->make(MessageFormatterService::class)
            );
        });


        $this->app->bind(AdminMessageForAssistantService::class, function ($app) { // <-- Nuevo nombre
            return new AdminMessageForAssistantService( // <-- Nuevo nombre
                $app->make(ThreadManagerService::class),
                $app->make(MessageFormatterService::class)
            );
        });

        // Binding para MessageFormatterService
        // Este servicio es vital para formatear mensajes para el frontend.
        $this->app->bind(MessageFormatterService::class, function ($app) {
            return new MessageFormatterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
