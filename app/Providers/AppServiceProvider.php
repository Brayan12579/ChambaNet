<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios de la aplicación.
     */
    public function register(): void
    {
    }

    /**
     * Inicializa los servicios de la aplicación.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $user = request()->user();
            $view->with('unreadNotificationCount', $user?->active ? $user->unreadNotifications()->count() : 0);
        });
    }
}
