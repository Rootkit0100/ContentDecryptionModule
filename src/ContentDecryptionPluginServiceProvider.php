<?php

namespace ContentDecryptionPlugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ContentDecryptionPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (config('app.type') != 'LB') {
            $this->loadRoutesFrom(__DIR__.'/../routes/bo.php');

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'content_decryption');

            $this->loadViewsFrom(__DIR__.'/../resources/views', 'content_decryption');

            $this->publishes([
                __DIR__.'/../resources/js' => public_path('js/scripts/'),
            ], 'public');
        }

        if (app()->runningInConsole()) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->job(new \ContentDecryptionPlugin\Jobs\RestartMpdRtmpJob())->everyMinute()->runInBackground();
            });
        }
    }

    public function register()
    {
        if (config('app.type') != 'LB') {
            $merge = [
                'routes_permissions',
                'reseller_permissions',
                'sidebar_permissions',
                'sidebar_entries',
            ];

            foreach ($merge as $c) {
                $this->mergeConfigFrom(
                    __DIR__.'/../config/' . $c . '.php',
                    $c
                );
            }
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/plugin.php',
            'plugins'
        );
    }

    protected function loadRoutesFrom($path)
    {
        require $path;
    }

    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app->make('config');

        $config->set($key, array_merge(
            require $path, $config->get($key, [])
        ));
    }
}
