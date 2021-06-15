<?php

namespace AlwaysOpen\ProcessStamps;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
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
                __DIR__ . '/../config/process-stamps.php' => config_path('process-stamps.php'),
            ], 'config');

            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/process-stamps.php', 'process-stamps');

        $config = $this->app['config']->get('process-stamps');

        Blueprint::macro('processIds', function (?array $params = []) use ($config) {
            $this->unsignedInteger($config['columns']['created'])
                ->nullable()
                ->index(Arr::get($params, 'created_index_name'));

            $this->unsignedInteger($config['columns']['updated'])
                ->nullable()
                ->index(Arr::get($params, 'updated_index_name'));

            $this->foreign($config['columns']['created'], Arr::get($params, 'created_foreign_key_name'))
                ->references($config['columns']['primary_key'])
                ->on($config['table']);

            $this->foreign($config['columns']['updated'], Arr::get($params, 'updated_foreign_key_name'))
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
