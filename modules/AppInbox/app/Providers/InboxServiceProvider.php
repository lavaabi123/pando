<?php

namespace Modules\AppInbox\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class InboxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'appinbox');
        
        // Load translations if needed
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'appinbox');

        // Publish assets if needed
        $this->publishes([
            __DIR__ . '/../../resources/assets' => public_path('modules/appinbox'),
        ], 'inbox-assets');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('inbox.php'),
        ], 'inbox-config');
    }
}
