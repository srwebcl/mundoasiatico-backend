<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // ── Registrar el Observer de Órdenes ─────────────────────────────────
        Order::observe(OrderObserver::class);

        // ── Forzar HTTPS en producción (necesario en cPanel con SSL) ──────────
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
