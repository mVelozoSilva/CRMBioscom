<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // 
use Carbon\Carbon; // Import Carbon for date localization

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

        Carbon::setLocale('es');   



    }
}