<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Agregamos esto

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
        // <-- Agregamos esto para forzar HTTPS en producción
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}