<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use KraenzleRitter\ResourcesComponents\GndLwComponent;
use KraenzleRitter\ResourcesComponents\MetagridLwComponent;
use KraenzleRitter\ResourcesComponents\WikidataLwComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;
use ReflectionClass;
use ReflectionMethod;

class LivewireComponentCompatibilityTest extends TestCase
{
    /**
     * Test that all Livewire components can handle the data structure
     * returned by their corresponding providers.
     */
    #[Test]
    #[DataProvider('livewireComponentProvider')]
    public function handlesProviderResponseStructureCorrectly(string $componentClass, string $providerName, $mockData)
    {
        // Create component instance
        $component = new $componentClass();
        
        // Get the processResults method via reflection
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        try {
            $results = $processMethod->invoke($component, $mockData);
            
            // Verify results is an array
            $this->assertTrue(is_array($results), "processResults should return an array for {$componentClass}");
            
            // If we have results, verify the structure
            if (!empty($results)) {
                foreach ($results as $result) {
                    $this->assertTrue(is_array($result), "Each result should be an array for {$componentClass}");
                    
                    // Check for required fields that are commonly used in views
                    $this->assertTrue(
                        array_key_exists('provider_id', $result) || array_key_exists('gndIdentifier', $result),
                        "Result should have provider_id or gndIdentifier for {$componentClass}"
                    );
                    $this->assertTrue(
                        array_key_exists('url', $result) || array_key_exists('uri', $result),
                        "Result should have url or uri for {$componentClass}"
                    );
                }
            }
        } catch (\TypeError $e) {
            $this->fail("TypeError in {$componentClass}::processResults: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail("Exception in {$componentClass}::processResults: " . $e->getMessage());
        }
    }

    /**
     * Test that Livewire components produce data that their corresponding 
     * Blade views can consume without errors.
     */
    #[Test]
    #[DataProvider('livewireComponentProvider')]
    public function producesViewCompatibleData(string $componentClass, string $providerName, $mockData)
    {
        $component = new $componentClass();
        
        // Get the processResults method via reflection
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        $results = $processMethod->invoke($component, $mockData);
        
        if (empty($results)) {
            $this->markTestSkipped("No results to test for {$componentClass}");
        }
        
        foreach ($results as $result) {
            // Test array access patterns commonly used in views
            $this->assertTrue(
                isset($result['provider_id']) || isset($result['gndIdentifier']),
                "Result should have provider_id or gndIdentifier for view compatibility in {$componentClass}"
            );
            
            $this->assertTrue(
                isset($result['url']) || isset($result['uri']),
                "Result should have url or uri for view compatibility in {$componentClass}"
            );
            
            // Test that JSON encoding works (needed for wire:click parameters)
            $jsonResult = json_encode($result, JSON_UNESCAPED_UNICODE);
            $this->assertNotFalse($jsonResult, "Result should be JSON encodable for {$componentClass}");
        }
    }

    /**
     * Test that the provider factory can create providers and they return
     * expected data structures.
     */
    #[Test]
    #[DataProvider('providerDataProvider')]
    public function createsProvidersWithExpectedInterface(string $providerName)
    {
        $provider = ProviderFactory::create($providerName);
        
        $this->assertNotNull($provider, "Provider {$providerName} should be creatable");
        $this->assertTrue(method_exists($provider, 'search'), "Provider {$providerName} should have search method");
        $this->assertTrue(method_exists($provider, 'getProviderName'), "Provider {$providerName} should have getProviderName method");
    }

    /**
     * Test specific known problematic scenarios that caused the original bugs.
     */
    #[Test]
    public function handlesGndResponseStructureCorrectly()
    {
        $component = new GndLwComponent();
        
        // Mock GND response structure (what the API actually returns)
        $mockGndResponse = (object) [
            'totalItems' => 1,
            'member' => [
                (object) [
                    'preferredName' => 'Test Person',
                    'gndIdentifier' => '123456789',
                    'type' => ['Person'],
                    'dateOfBirth' => ['1900-01-01'],
                    'dateOfDeath' => ['2000-01-01'],
                    'biographicalOrHistoricalInformation' => ['Test biography']
                ]
            ]
        ];
        
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        $results = $processMethod->invoke($component, $mockGndResponse);
        
        $this->assertTrue(is_array($results));
        $this->assertNotEmpty($results);
        
        $result = $results[0];
        $this->assertTrue(array_key_exists('gndIdentifier', $result));
        $this->assertTrue(array_key_exists('dateOfBirth', $result));
        $this->assertTrue(array_key_exists('dateOfDeath', $result));
        $this->assertTrue(array_key_exists('biographicalOrHistoricalInformation', $result));
    }

    #[Test]
    public function handlesMetagridResponseStructureCorrectly()
    {
        $component = new MetagridLwComponent();
        
        // Mock Metagrid response structure (array of stdClass objects)
        $mockMetagridResponse = [
            (object) [
                'id' => '12345',
                'uri' => 'https://example.com/12345',
                'metadata' => (object) [
                    'first_name' => 'Test',
                    'last_name' => 'Person',
                    'birth_date' => '1900-01-01',
                    'death_date' => '2000-01-01'
                ]
            ]
        ];
        
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        $results = $processMethod->invoke($component, $mockMetagridResponse);
        
        $this->assertTrue(is_array($results));
        $this->assertNotEmpty($results);
        
        $result = $results[0];
        $this->assertTrue(array_key_exists('provider_id', $result));
        $this->assertTrue(array_key_exists('url', $result));
        $this->assertTrue(array_key_exists('preferredName', $result));
    }

    /**
     * Data provider for Livewire components with mock data
     */
    public static function livewireComponentProvider(): array
    {
        return [
            'GND Component' => [
                GndLwComponent::class,
                'gnd',
                (object) [
                    'totalItems' => 1,
                    'member' => [
                        (object) [
                            'preferredName' => 'Test Person',
                            'gndIdentifier' => '123456789',
                            'type' => ['Person'],
                            'dateOfBirth' => ['1900-01-01'],
                            'dateOfDeath' => ['2000-01-01'],
                            'biographicalOrHistoricalInformation' => ['Test info']
                        ]
                    ]
                ]
            ],
            'Metagrid Component' => [
                MetagridLwComponent::class,
                'metagrid',
                [
                    (object) [
                        'id' => '12345',
                        'uri' => 'https://example.com/12345',
                        'metadata' => (object) [
                            'first_name' => 'Test',
                            'last_name' => 'Person'
                        ]
                    ]
                ]
            ],
            'Wikidata Component' => [
                WikidataLwComponent::class,
                'wikidata',
                (object) [
                    'search' => [
                        (object) [
                            'id' => 'Q123',
                            'label' => 'Test Person',
                            'description' => 'Test description'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider for testing provider factory
     */
    public static function providerDataProvider(): array
    {
        return [
            ['gnd'],
            ['metagrid'],
            ['wikidata'],
            ['wikipedia'],
            ['geonames'],
            ['idiotikon'],
            ['ortsnamen'],
            ['anton']
        ];
    }
}
