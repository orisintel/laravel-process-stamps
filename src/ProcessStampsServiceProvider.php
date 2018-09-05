<?php

namespace OrisIntel\ProcessStamps;

use Illuminate\Support\ServiceProvider;

class ProcessStampsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/process-stamps.php' => config_path('process-stamps.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../migrations' => database_path('migrations'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/process-stamps.php', 'process-stamps');
    }
}
