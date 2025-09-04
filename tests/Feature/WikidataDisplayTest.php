<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Wikidata;

class WikidataDisplayTest extends TestCase
{
    /**
     * Test that Wikidata search results have both label and title fields for proper display
     */
    public function test_wikidata_results_have_proper_display_fields()
    {
        // Mock the Wikidata API response
        $mockResponse = [
            'search' => [
                [
                    'id' => 'Q937',
                    'label' => 'Albert Einstein',
                    'description' => 'German-born theoretical physicist',
                ],
                [
                    'id' => 'Q1234',
                    'label' => 'Test Entity',
                    'description' => 'Test description',
                ]
            ]
        ];

        // Create a Wikidata instance
        $wikidata = new Wikidata();

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($wikidata);
        $method = $reflection->getMethod('processSearchResults');
        $method->setAccessible(true);

        // Process the mock results
        $results = $method->invoke($wikidata, $mockResponse['search'], 'de');

        // Verify that results have proper structure for display
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        $firstResult = $results[0];

        // Check that both 'label' and 'title' fields exist (for compatibility)
        $this->assertObjectHasProperty('label', $firstResult);
        $this->assertObjectHasProperty('title', $firstResult);
        $this->assertObjectHasProperty('description', $firstResult);
        $this->assertObjectHasProperty('id', $firstResult);
        $this->assertObjectHasProperty('url', $firstResult);

        // Verify the values
        $this->assertEquals('Albert Einstein', $firstResult->label);
        $this->assertEquals('Albert Einstein', $firstResult->title);
        $this->assertEquals('German-born theoretical physicist', $firstResult->description);
        $this->assertEquals('Q937', $firstResult->id);
        $this->assertEquals('https://www.wikidata.org/wiki/Q937', $firstResult->url);
    }

    /**
     * Test that Wikidata results handle missing label gracefully
     */
    public function test_wikidata_results_handle_missing_label()
    {
        // Mock response with missing label
        $mockResponse = [
            'search' => [
                [
                    'id' => 'Q9999',
                    // No label field
                    'description' => 'Test entity without label',
                ]
            ]
        ];

        $wikidata = new Wikidata();

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($wikidata);
        $method = $reflection->getMethod('processSearchResults');
        $method->setAccessible(true);

        $results = $method->invoke($wikidata, $mockResponse['search'], 'de');

        $this->assertNotEmpty($results);
        $firstResult = $results[0];

        // Should fallback to ID when no label is present
        $this->assertEquals('Q9999', $firstResult->label);
        $this->assertEquals('Q9999', $firstResult->title);
    }
}
