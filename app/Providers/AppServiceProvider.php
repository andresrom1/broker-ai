<?php

namespace App\Providers;

use App\Http\Controllers\QuoteAlternativeController;
use App\Services\Quotes\QuoteAlternativeService;
use App\Services\Quotes\QuoteRequestService;
use Illuminate\Support\ServiceProvider;

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
                $app->make(QuoteRequestService::class) // Inyecta QuoteRequestService
                // Si QuoteAlternativeService tuviera más dependencias, las inyectarías aquí también
                // $app->make(ThreadManagerService::class),
                // $app->make(MessageFormatterService::class)
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
