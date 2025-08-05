<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

/**
 * Consolidated test for basic provider resource lifecycle operations.
 * Replaces individual provider test files that all tested the same CRUD pattern.
 */
class ProviderLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    /**
     * Data provider for all basic providers
     */
    public static function providerDataProvider()
    {
        return [
            'Wikipedia DE' => [
                'wikipedia-de',
                '12345',
                'https://de.wikipedia.org/wiki/Albert_Einstein'
            ],
            'Wikipedia EN' => [
                'wikipedia-en',
                'Albert_Einstein',
                'https://en.wikipedia.org/wiki/Albert_Einstein'
            ],
            'Wikidata' => [
                'wikidata',
                'Q937',
                'https://www.wikidata.org/wiki/Q937'
            ],
            'GND' => [
                'gnd',
                '118529579',
                'https://d-nb.info/gnd/118529579'
            ],
            'Geonames' => [
                'geonames',
                '2661604',
                'https://www.geonames.org/2661604/'
            ],
            'Metagrid' => [
                'metagrid',
                '12345',
                'https://metagrid.ch/person/12345'
            ],
            'Idiotikon' => [
                'idiotikon',
                'L12345',
                'https://digital.idiotikon.ch/p/lem/L12345'
            ],
            'Ortsnamen' => [
                'ortsnamen',
                '12345',
                'https://ortsnamen.ch/12345'
            ],
            'Anton GeorgFischer' => [
                'georgfischer',
                '12345',
                'https://archives.georgfischer.com/person/12345'
            ],
            'Manual Input' => [
                'manual-input',
                'manual-123',
                'https://example.com/manual/123'
            ]
        ];
    }

    /**
     * Test basic resource lifecycle for all providers
     *
     * @dataProvider providerDataProvider
     */
    public function test_provider_resource_lifecycle($provider, $providerId, $url)
    {
        // 1. Create model
        $model = DummyModel::create(['name' => "Test {$provider} Model"]);

        // 2. Add resource
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url
        ]);

        // 3. Check resource exists
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "The {$provider} resource was not successfully added");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "The resource does not have the correct provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "The resource does not have the correct URL");

        // 4. Remove resource
        $model->removeResource($providerId);

        // 5. Check resource is removed
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "The {$provider} resource was not successfully removed");
    }

    /**
     * Test resource with full JSON data
     */
    public function test_resource_with_full_json()
    {
        $provider = 'idiotikon';
        $providerId = 'L12345';
        $url = 'https://digital.idiotikon.ch/p/lem/L12345';
        $fullJson = json_encode([
            'lemmaID' => 'L12345',
            'lemmaText' => 'Züri',
            'url' => 'https://api.idiotikon.ch/lemma/L12345',
            'description' => ['A city in Switzerland']
        ]);

        // Create model
        $model = DummyModel::create(['name' => 'Test Model with JSON']);

        // Add resource with full JSON
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => $fullJson
        ]);

        // Check resource exists
        $this->assertTrue($model->resources->contains('provider_id', $providerId));
        $this->assertTrue($model->resources->contains('provider', $provider));
        $this->assertTrue($model->resources->contains('url', $url));
    }

    /**
     * Test duplicate resource handling - basic test
     */
    public function test_duplicate_resource_handling()
    {
        $provider = 'wikipedia-de';
        $providerId = '12345';
        $url1 = 'https://de.wikipedia.org/wiki/Albert_Einstein';

        $model = DummyModel::create(['name' => 'Test Duplicate Model']);

        // Add resource first time
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url1
        ]);

        // Check resource was added
        $this->assertTrue($model->resources->contains('provider_id', $providerId));
        $this->assertTrue($model->resources->contains('url', $url1));

        // Add same resource again - should not create duplicates (handled by underlying package)
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url1
        ]);

        // Should still work without errors
        $this->assertTrue($model->resources->contains('provider_id', $providerId));
    }
}
