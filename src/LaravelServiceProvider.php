<?php

namespace NovaKit\NovaOnVapor;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Vapor\Contracts\SignedStorageUrlController as SignedStorageUrlControllerContract;
use NovaKit\NovaOnVapor\Http\Controllers\SignedStorageUrlController;
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

        if (config('nova-on-vapor.minio.enabled') === true) {
            $this->app->singleton(
                SignedStorageUrlControllerContract::class,
                SignedStorageUrlController::class
            );
        }
    }
}
