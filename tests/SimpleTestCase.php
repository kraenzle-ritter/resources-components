<?php

namespace KraenzleRitter\ResourcesComponents\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider;

abstract class SimpleTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        // No database setup - we're testing providers directly
    }

    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            ResourcesComponentsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Only minimal config setup, no database
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        // Set up minimal config for our providers
        $app['config']->set('resources-components.limit', 5);
        $app['config']->set('resources-components.providers.wikidata.target_url', 'https://www.wikidata.org/wiki/{provider_id}');
        $app['config']->set('resources-components.providers.gnd.target_url', 'https://d-nb.info/gnd/{provider_id}');
        $app['config']->set('resources-components.providers.idiotikon.target_url', 'https://digital.idiotikon.ch/p/lem/{provider_id}');
        $app['config']->set('resources-components.providers.geonames.user_name', 'demo');
    }
}
