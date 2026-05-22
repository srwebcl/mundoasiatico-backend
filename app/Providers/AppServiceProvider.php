<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
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
        // ── Registrar el Observer de Órdenes y Usuarios ──────────────────────
        Order::observe(OrderObserver::class);
        User::observe(UserObserver::class);

        // ── Forzar HTTPS en producción (necesario en cPanel con SSL) ──────────
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            
            // Re-configurar el disco 'public' para que guarde directo en la carpeta pública
            // Esto soluciona de forma permanente el problema de symlinks en cPanel
            config([
                'filesystems.disks.public.root' => public_path('storage'),
                'filesystems.disks.public.url' => config('app.url').'/storage',
                'filesystems.disks.public.visibility' => 'public',
            ]);
        }
    }
}
