<?php

namespace NovaKit\NovaOnVapor\Tests\Fixtures\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use NovaKit\NovaOnVapor\Tests\Fixtures\Nova\User;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function ($event) {
            Nova::resources([
                User::class,
            ]);
        });
    }
}
