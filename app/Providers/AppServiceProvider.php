<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // ¡Importa la fachada Route!

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cargar rutas de API desde routes/api.php
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        // Aquí puede que ya tengas la carga de routes/web.php
        // Si ya tienes un Route::middleware('web')->group(base_path('routes/web.php'));
        // déjalo como está. Si no lo tienes y tus rutas web funcionan, no lo añadas.
    }
}