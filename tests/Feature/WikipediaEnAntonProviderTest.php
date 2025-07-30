<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;

class WikipediaEnAntonProviderTest extends TestCase
{
    /**
     * Test if the English Wikipedia provider is correctly configured.
     *
     * @return void
     */
    public function test_wikipedia_en_provider_is_properly_configured()
    {
        $this->assertTrue(
            config()->has('resources-components.providers.wikipedia-en'),
            'English Wikipedia provider configuration is missing'
        );

        $this->assertEquals(
            'https://en.wikipedia.org/w/api.php',
            config('resources-components.providers.wikipedia-en.base_url'),
            'English Wikipedia base URL is not correctly configured'
        );
    }

    /**
     * Test that at least one Anton-type provider is configured properly
     *
     * @return void
     */
    public function test_anton_provider_is_properly_configured()
    {
        // Check if at least one Anton-type provider exists
        $antonProviders = collect(config('resources-components.providers'))
            ->filter(function ($config) {
                return isset($config['api-type']) && $config['api-type'] === 'Anton';
            });

        $this->assertGreaterThan(
            0,
            $antonProviders->count(),
            'No Anton-type provider configuration found'
        );

        // Check if the first Anton provider has the required configuration
        $firstAntonProvider = $antonProviders->keys()->first();
        $this->assertNotNull($firstAntonProvider, 'No Anton provider found');

        $config = config("resources-components.providers.{$firstAntonProvider}");
        $this->assertTrue(
            isset($config['base_url']),
            "Anton provider '{$firstAntonProvider}' is missing base_url configuration"
        );
    }
}
