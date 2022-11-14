<?php

namespace NovaKit\NovaOnVapor\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Automatically enables package discoveries.
     *
     * @var bool
     */
    protected $enablesPackageDiscoveries = true;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \NovaKit\NovaOnVapor\LaravelServiceProvider::class,
            \NovaKit\NovaOnVapor\Tests\NovaServiceProvider::class,
        ];
    }

    /**
     * Ignore package discovery from.
     *
     * @return array
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return [];
    }
}
