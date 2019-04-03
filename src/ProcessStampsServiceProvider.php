<?php

namespace OrisIntel\ProcessStamps;

use Illuminate\Database\Schema\Blueprint;
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

            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/process-stamps.php', 'process-stamps');

        $config = $this->app['config']->get('process-stamps');

        Blueprint::macro('processIds', function () use ($config) {
            $this->unsignedInteger($config['columns']['created'])->nullable()->index();
            $this->unsignedInteger($config['columns']['updated'])->nullable()->index();

            $this->foreign($config['columns']['created'])
                ->references($config['columns']['primary_key'])
                ->on($config['table']);

            $this->foreign($config['columns']['updated'])
                ->references($config['columns']['primary_key'])
                ->on($config['table']);
        });

        Blueprint::macro('dropProcessIds', function () use ($config) {
            $this->dropColumn([
                $config['columns']['created'],
                $config['columns']['updated'],
            ]);
        });
    }
}
