<?php

namespace NovaKit\NovaOnVapor;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Symfony\Component\Console\Input\InputArgument;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\UserCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/nova-on-vapor.php', 'nova-on-vapor');
        }
    }
}
