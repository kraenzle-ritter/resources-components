<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\SimpleTestCase;
use KraenzleRitter\ResourcesComponents\Testing\ProviderTestHelper;

class ProviderFunctionalityTest extends SimpleTestCase
{
    /**
     * Test Metagrid provider functionality directly
     */
    public function test_metagrid_provider_functionality()
    {
        $result = ProviderTestHelper::testMetagridProvider('Albert Einstein');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('metagrid', $result['provider']);
        $this->assertIsObject($result['data']);
    }

    /**
     * Test Wikidata provider functionality
     */
    public function test_wikidata_provider_functionality()
    {
        $result = ProviderTestHelper::testWikidataProvider('Albert Einstein');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('wikidata', $result['provider']);
    }

    /**
     * Test Wikipedia provider functionality
     */
    public function test_wikipedia_provider_functionality()
    {
        $result = ProviderTestHelper::testWikipediaProvider('de', 'Albert Einstein');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('wikipedia-de', $result['provider']);
    }

    /**
     * Test GND provider functionality
     */
    public function test_gnd_provider_functionality()
    {
        $result = ProviderTestHelper::testGndProvider('Albert Einstein');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('gnd', $result['provider']);
    }

    /**
     * Test Geonames provider functionality (may be skipped if no username)
     */
    public function test_geonames_provider_functionality()
    {
        $result = ProviderTestHelper::testGeonamesProvider('Zürich');

        if ($result['error'] === 'No valid username found') {
            $this->markTestSkipped('Geonames test skipped: No valid username configured');
        }

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('geonames', $result['provider']);
    }

    /**
     * Test Idiotikon provider functionality
     */
    public function test_idiotikon_provider_functionality()
    {
        $result = ProviderTestHelper::testIdiotikonProvider('Allmend');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('idiotikon', $result['provider']);
    }

    /**
     * Test Ortsnamen provider functionality
     */
    public function test_ortsnamen_provider_functionality()
    {
        $result = ProviderTestHelper::testOrtsnamenProvider('Zürich');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('ortsnamen', $result['provider']);
    }

    /**
     * Test Anton provider functionality
     */
    public function test_anton_provider_functionality()
    {
        $result = ProviderTestHelper::testAntonProvider('georgfischer', 'archiv', 'actors');

        $this->assertTrue($result['success'], $result['error'] ?? 'Test failed');
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['provider_id']);
        $this->assertEquals('georgfischer', $result['provider']);
    }

    /**
     * Test URL generation
     */
    public function test_provider_url_generation()
    {
        $url = ProviderTestHelper::getProviderUrl('wikidata', 'Q937');
        $this->assertNotNull($url);
        $this->assertStringContainsString('Q937', $url);

        $url = ProviderTestHelper::getProviderUrl('gnd', '118529579');
        $this->assertNotNull($url);
        $this->assertStringContainsString('118529579', $url);
    }

    /**
     * Test URL extraction from API responses
     */
    public function test_url_extraction_from_api_responses()
    {
        // Test for Idiotikon
        $idiotikonResult = (object)[
            'lemmaID' => 'L12345',
            'url' => 'https://api.idiotikon.ch/lemma/L12345'
        ];

        $url = ProviderTestHelper::getProviderUrl('idiotikon', $idiotikonResult->lemmaID);
        $this->assertEquals('https://digital.idiotikon.ch/p/lem/L12345', $url);

        // Test for Metagrid
        $metagridResult = (object)[
            'id' => '12345',
            'resources' => [
                (object)[
                    'link' => (object)[
                        'uri' => 'https://hls-dhs-dss.ch/123'
                    ]
                ]
            ]
        ];

        // Extract URL from resources
        $urlFromResources = '';
        if (isset($metagridResult->resources) && is_array($metagridResult->resources) && !empty($metagridResult->resources)) {
            foreach ($metagridResult->resources as $resource) {
                if (isset($resource->link) && isset($resource->link->uri)) {
                    $urlFromResources = $resource->link->uri;
                    break;
                }
            }
        }

        $this->assertEquals('https://hls-dhs-dss.ch/123', $urlFromResources);
    }
}
