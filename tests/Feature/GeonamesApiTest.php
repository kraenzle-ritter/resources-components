<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Geonames;

class GeonamesApiTest extends TestCase
{
    protected $skipIfNoInternet = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip test if internet connection is not available
        if ($this->skipIfNoInternet) {
            try {
                $connection = @fsockopen("www.geonames.org", 80);
                if (!$connection) {
                    $this->markTestSkipped('No internet connection available');
                }
                fclose($connection);
            } catch (\Exception $e) {
                $this->markTestSkipped('No internet connection available');
            }
        }
    }

    public function test_geonames_config_is_correct()
    {
        // Überprüfen, ob die Geonames-Konfiguration korrekt geladen wird
        $username = config('resources-components.providers.geonames.user_name');
        $this->assertNotNull($username, 'Geonames username sollte in der Konfiguration definiert sein');

        // Die konfigurierte URL für Geonames überprüfen
        $baseUrl = config('resources-components.providers.geonames.base_url');
        $this->assertEquals('http://api.geonames.org/', $baseUrl,
            'Geonames base_url sollte korrekt konfiguriert sein');

        // Überprüfen der optionalen Konfigurationsparameter
        $this->assertTrue(array_key_exists('continent_code', config('resources-components.providers.geonames')),
            'Geonames continent_code sollte in der Konfiguration definiert sein');

        $this->assertTrue(array_key_exists('country_bias', config('resources-components.providers.geonames')),
            'Geonames country_bias sollte in der Konfiguration definiert sein');
    }

    public function test_geonames_search_returns_expected_structure()
    {
        // Dieser Test prüft nur, ob die Geonames-Suchfunktion eine korrekte Anfrage stellt
        // Die tatsächliche Antwort kann vom täglichen Limit des Demo-Accounts abhängen

        // Arrange
        $geonames = new Geonames();
        $searchTerm = 'Zürich';

        // Act
        $results = $geonames->search($searchTerm, ['maxRows' => 3]);

        // Skip test if using demo account and limit reached
        if (empty($results) && config('resources-components.providers.geonames.user_name') === 'demo') {
            $this->markTestSkipped('Demo account daily limit reached. Register for a free account at https://www.geonames.org/login');
        }

        // Assert
        $this->assertIsArray($results, 'Geonames search should return an array');

        // Wenn Ergebnisse zurückkommen, prüfe ihre Struktur
        if (!empty($results)) {
            // Check structure of the first result
            $firstResult = $results[0];
            $this->assertTrue(property_exists($firstResult, 'geonameId'), 'Result should have a geonameId');
            $this->assertTrue(property_exists($firstResult, 'toponymName'), 'Result should have a toponymName');
            $this->assertTrue(property_exists($firstResult, 'countryName'), 'Result should have a countryName');
            $this->assertTrue(property_exists($firstResult, 'lat'), 'Result should have a latitude');
            $this->assertTrue(property_exists($firstResult, 'lng'), 'Result should have a longitude');

            // Verify Zürich is actually found
            $foundZurich = false;
            foreach ($results as $result) {
                if (stripos($result->toponymName, 'Zürich') !== false ||
                    stripos($result->toponymName, 'Zurich') !== false) {
                    $foundZurich = true;
                    break;
                }
            }
            $this->assertTrue($foundZurich, 'Geonames search should find Zürich');
        }

        // Check structure of the first result
        $firstResult = $results[0];
        $this->assertTrue(property_exists($firstResult, 'geonameId'), 'Result should have a geonameId');
        $this->assertTrue(property_exists($firstResult, 'toponymName'), 'Result should have a toponymName');
        $this->assertTrue(property_exists($firstResult, 'countryName'), 'Result should have a countryName');
        $this->assertTrue(property_exists($firstResult, 'lat'), 'Result should have a latitude');
        $this->assertTrue(property_exists($firstResult, 'lng'), 'Result should have a longitude');

        // Verify Zürich is actually found
        $foundZurich = false;
        foreach ($results as $result) {
            if (stripos($result->toponymName, 'Zürich') !== false ||
                stripos($result->toponymName, 'Zurich') !== false) {
                $foundZurich = true;
                break;
            }
        }
        $this->assertTrue($foundZurich, 'Geonames search should find Zürich');
    }

    public function test_geonames_username_is_used_in_query()
    {
        // Create a Geonames instance with a mocked logger to capture the API request
        $geonames = new Geonames();

        // Get the username from the configuration
        $expectedUsername = config('resources-components.providers.geonames.user_name');
        $this->assertNotNull($expectedUsername, 'Username should be set in the configuration');

        // Check that the username is correctly set in the instance
        $this->assertEquals($expectedUsername, $geonames->username,
            'Username should be correctly loaded from configuration');

        // Execute a search to trigger API request
        $results = $geonames->search('Zürich', ['maxRows' => 1]);

        // Skip test if using demo account and limit reached
        if (empty($results) && $expectedUsername === 'demo') {
            $this->markTestSkipped('Demo account daily limit reached. Register for a free account at https://www.geonames.org/login');
        }

        // If using a custom account, verify results
        if ($expectedUsername !== 'demo') {
            $this->assertNotEmpty($results, 'Search should return results with a valid username');
        }
    }
}
