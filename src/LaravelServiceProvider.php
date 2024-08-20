<?php

namespace NovaKit\NovaOnVapor;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Fields\VaporFile;
use Laravel\Vapor\Contracts\SignedStorageUrlController as SignedStorageUrlControllerContract;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerCommands();
        $this->registerFieldsMacros();
    }

    /**
     * Register the tool's commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\UserCommand::class,
            ]);
        }
    }

    /**
     * Register fields macros.
     */
    protected function registerFieldsMacros(): void
    {
        VaporFile::mixin(new Fields\VaporFileMixins);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
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
     */
    protected function routes(): void
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
