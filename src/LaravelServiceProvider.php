<?php

namespace NovaKit\NovaOnVapor;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authorize;
use Laravel\Nova\Nova;
use Laravel\Vapor\Contracts\SignedStorageUrlController as SignedStorageUrlControllerContract;
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
        $this->app->booted(function () {
            $this->routes();
        });

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/nova-on-vapor.php', 'nova-on-vapor');
        }

        if (config('nova-on-vapor.minio.enabled') === true) {
            $this->app->singleton(
                SignedStorageUrlControllerContract::class,
                Http\Controllers\SignedStorageUrlController::class
            );
        }
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/nova-on-vapor')
            ->group(function (Router $router) {
                $router->get('downloads', Http\Controllers\DownloadsController::class)->name('nova-on-vapor.download');
            });
    }
}
