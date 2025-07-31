<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use Illuminate\Support\Facades\Config;

class WikipediaProviderCheckTest extends TestCase
{
    /**
     * Test that Wikipedia search returns proper structure for provider check
     */
    public function test_wikipedia_search_returns_proper_structure()
    {
        // Set up Wikipedia configuration
        Config::set('resources-components.providers.wikipedia-de', [
            'label' => 'Wikipedia (DE)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://de.wikipedia.org/w/api.php',
            'target_url' => 'https://de.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Bertha von Suttner'
        ]);

        $wikipedia = new Wikipedia();
        $results = $wikipedia->search('Albert Einstein', [
            'providerKey' => 'wikipedia-de',
            'locale' => 'de',
            'limit' => 1
        ]);

        $this->assertNotEmpty($results, 'Wikipedia should return results');
        $this->assertIsArray($results, 'Wikipedia should return an array');

        if (!empty($results)) {
            $firstResult = $results[0];
            $this->assertIsObject($firstResult, 'First result should be an object');
            $this->assertObjectHasProperty('title', $firstResult, 'Result should have title property');
            $this->assertObjectHasProperty('pageid', $firstResult, 'Result should have pageid property');

            // Optional properties that should exist for proper display
            if (property_exists($firstResult, 'snippet')) {
                $this->assertIsString($firstResult->snippet, 'Snippet should be a string');
            }
        }
    }

    /**
     * Test that Wikipedia results work with provider check view logic
     */
    public function test_wikipedia_results_work_with_view_logic()
    {
        $mockWikipediaResult = (object) [
            'pageid' => 12345,
            'title' => 'Albert Einstein',
            'snippet' => 'Albert Einstein was a <span>theoretical physicist</span> who developed the theory of relativity.'
        ];

        // Test the logic that would be used in the view
        $provider_id = $mockWikipediaResult->pageid;
        $name = $mockWikipediaResult->title;
        $desc = strip_tags($mockWikipediaResult->snippet);

        $this->assertEquals(12345, $provider_id);
        $this->assertEquals('Albert Einstein', $name);
        $this->assertEquals('Albert Einstein was a theoretical physicist who developed the theory of relativity.', $desc);

        // Test URL generation
        $targetUrlTemplate = 'https://de.wikipedia.org/wiki/{underscored_name}';
        $expectedUrl = str_replace('{underscored_name}', str_replace(' ', '_', $name), $targetUrlTemplate);

        $this->assertEquals('https://de.wikipedia.org/wiki/Albert_Einstein', $expectedUrl);
    }

    /**
     * Test Wikipedia results array format
     */
    public function test_wikipedia_results_array_format()
    {
        $mockWikipediaResultArray = [
            'pageid' => 54321,
            'title' => 'Marie Curie',
            'snippet' => 'Marie Curie was a <span>physicist</span> and chemist.'
        ];

        // Test array-based logic
        $provider_id = $mockWikipediaResultArray['pageid'];
        $name = $mockWikipediaResultArray['title'];
        $desc = strip_tags($mockWikipediaResultArray['snippet']);

        $this->assertEquals(54321, $provider_id);
        $this->assertEquals('Marie Curie', $name);
        $this->assertEquals('Marie Curie was a physicist and chemist.', $desc);
    }
}
