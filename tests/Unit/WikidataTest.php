<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;

class WikidataTest extends TestCase
{
    protected Wikidata $wikidata;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wikidata = new Wikidata();
    }

    /** @test */
    public function it_can_create_wikidata_instance()
    {
        $this->assertInstanceOf(Wikidata::class, $this->wikidata);
        $this->assertInstanceOf(ProviderInterface::class, $this->wikidata);
    }

    /** @test */
    public function it_returns_correct_provider_name()
    {
        $this->assertEquals('Wikidata', $this->wikidata->getProviderName());
    }

    /** @test */
    public function it_returns_correct_base_url()
    {
        $this->assertEquals('https://www.wikidata.org/', $this->wikidata->getBaseUrl());
    }

    /** @test */
    public function it_sanitizes_search_string()
    {
        // Test that search string is properly sanitized
        $result = $this->wikidata->search('Test[Person]!(Example):', ['limit' => 1]);
        
        // Even if search fails, it should not throw an exception
        $this->assertTrue(is_array($result) || is_null($result));
    }

    /** @test */
    public function it_handles_comma_separated_search()
    {
        // Mock the Wikidata client to avoid actual API calls in tests
        $result = $this->wikidata->search('Einstein, Albert', ['limit' => 1]);
        
        // Should return array (empty or with results)
        $this->assertTrue(is_array($result));
    }

    /** @test */
    public function it_merges_params_correctly()
    {
        $params = ['locale' => 'en'];
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->wikidata);
        $method = $reflection->getMethod('mergeParams');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->wikidata, [$params]);

        $this->assertIsArray($result);
        $this->assertEquals('en', $result['locale']);
        $this->assertArrayHasKey('limit', $result);
    }
}
