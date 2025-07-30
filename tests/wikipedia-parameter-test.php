<?php

// Dieser Test überprüft, ob der providerKey richtig übergeben wird
// Führen Sie diesen Test mit "vendor/bin/phpunit tests/wikipedia-parameter-test.php" aus

namespace Tests;

use Tests\TestCase;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider;

class WikipediaParameterTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ResourcesComponentsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('resources-components.providers', [
            'wikipedia-de' => [
                'label' => 'Wikipedia (de)',
                'api-type' => 'Wikipedia',
                'base_url' => 'https://de.wikipedia.org/w/api.php',
            ],
            'wikipedia-fr' => [
                'label' => 'Wikipedia (fr)',
                'api-type' => 'Wikipedia',
                'base_url' => 'https://fr.wikipedia.org/w/api.php',
            ],
            'wikipedia-en' => [
                'label' => 'Wikipedia (en)',
                'api-type' => 'Wikipedia',
                'base_url' => 'https://en.wikipedia.org/w/api.php',
            ],
        ]);
    }

    // Mock für das Modell
    protected function createMockModel()
    {
        return new class {
            public $name = 'Test Model';
            public $resources = [];

            public function load() {
                return $this;
            }
        };
    }

    /** @test */
    public function test_it_uses_correct_provider_key()
    {
        // Test 1: Mit französischem Provider-Key
        $component = new WikipediaLwComponent();
        $component->mount($this->createMockModel(), "Paris", "wikipedia-fr");

        $this->assertStringContainsString('fr.wikipedia.org', $component->base_url);
        $this->assertEquals('wikipedia-fr', $component->queryOptions['providerKey']);

        // Test 2: Mit Standard-Provider-Key (sollte auf deutsch zurückfallen)
        $component = new WikipediaLwComponent();
        $component->mount($this->createMockModel(), "Paris");

        $this->assertStringContainsString('de.wikipedia.org', $component->base_url);
        $this->assertEquals('wikipedia-de', $component->queryOptions['providerKey']);

        // Test 3: Mit ungültigem Provider-Key (sollte auf deutsch zurückfallen)
        $component = new WikipediaLwComponent();
        $component->mount($this->createMockModel(), "Paris", "invalid-key");

        $this->assertStringContainsString('de.wikipedia.org', $component->base_url);
        $this->assertEquals('wikipedia-de', $component->queryOptions['providerKey']);
    }
}
