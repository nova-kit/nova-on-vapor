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
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
        $this->registerFieldsMacros();
    }

    /**
     * Register the tool's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\UserCommand::class,
            ]);
        }
    }

    /**
     * Register fields macros.
     *
     * @return void
     */
    protected function registerFieldsMacros()
    {
        VaporFile::mixin(new Fields\VaporFileMixins());
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
