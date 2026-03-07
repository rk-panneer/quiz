<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\LLM\LLMManager::class, function ($app) {
            return new \App\Services\LLM\LLMManager($app);
        });

        $this->app->bind(\App\Services\LLM\Contracts\LLMProviderInterface::class, function ($app) {
            return $app->make(\App\Services\LLM\LLMManager::class)->driver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('export', function ($user) {
            return true; // Simplified for admin access from Filament panel
        });

        Gate::define('export_ip', function ($user) {
            return true; // Allows IP export
        });
    }
}
