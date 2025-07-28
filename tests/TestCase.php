<?php

namespace KraenzleRitter\ResourcesComponents\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider;
use Livewire\LivewireServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            ResourcesComponentsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
