<?php

namespace KraenzleRitter\ResourcesComponents\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider;
use Livewire\LivewireServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            ResourcesComponentsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up database
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up cache
        config()->set('cache.default', 'array');
        
        // Set up basic app configuration
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        
        // Load resources-components configuration
        config()->set('resources-components', [
            'cache' => [
                'ttl' => 3600,
                'prefix' => 'resources_components',
                'enabled' => true,
            ],
            'geonames' => [
                'username' => 'test',
                'limit' => 5,
            ],
            'gnd' => [
                'limit' => 5
            ],
            'anton' => [
                'provider-slug' => 'anton',
                'token' => 'test',
                'url' => 'https://test.anton.ch',
                'api_url' => 'https://test.anton.ch/api',
                'limit' => 5
            ],
            'idiotikon' => [
                'limit' => 5,
            ],
            'metagrid' => [
                'limit' => 5
            ],
            'wikipedia' => [
                'limit' => 5
            ],
            'wikidata' => [
                'limit' => 5,
                'locale' => 'de'
            ],
            'ortsnamen' => [
                'limit' => 5,
                'locale' => 'de'
            ]
        ]);
        
        // Set up session for Livewire
        config()->set('session.driver', 'array');
    }
}
