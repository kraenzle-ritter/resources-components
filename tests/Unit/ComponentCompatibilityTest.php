<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use KraenzleRitter\ResourcesComponents\GndLwComponent;
use KraenzleRitter\ResourcesComponents\MetagridLwComponent;
use KraenzleRitter\ResourcesComponents\WikidataLwComponent;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use KraenzleRitter\ResourcesComponents\GeonamesLwComponent;
use KraenzleRitter\ResourcesComponents\IdiotikonLwComponent;
use KraenzleRitter\ResourcesComponents\OrtsnamenLwComponent;
use KraenzleRitter\ResourcesComponents\AntonLwComponent;
use ReflectionClass;

/**
 * Data type compatibility tests for Livewire components.
 * These tests would have caught the original type-related bugs.
 */
class ComponentCompatibilityTest extends TestCase
{
    /**
     * Test that GND component handles object-to-array conversion without throwing exceptions.
     * This would have caught the original "Attempt to read property on array" bug.
     */
    #[Test]
    public function gndComponentProcessesObjectToArrayWithoutTypeErrors(): void
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
        
        // This should not throw any TypeError or Exception
        $results = $processMethod->invoke($component, $mockGndResponse);
        
        // Basic validation
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        
        $result = $results[0];
        
        // These fields should exist and be accessible as array elements
        $this->assertArrayHasKey('gndIdentifier', $result);
        $this->assertArrayHasKey('dateOfBirth', $result);
        $this->assertArrayHasKey('dateOfDeath', $result);
        $this->assertArrayHasKey('biographicalOrHistoricalInformation', $result);
        
        // Test JSON encoding (critical for Livewire wire:click)
        $jsonResult = json_encode($result);
        $this->assertNotFalse($jsonResult);
    }

    /**
     * Test that Metagrid component handles stdClass objects without throwing exceptions.
     * This would have caught the original "Cannot use object of type stdClass as array" bug.
     * 
     * Note: This test is skipped because MetagridLwComponent uses Laravel facades
     * which are not available in standalone PHPUnit tests.
     */
    #[Test]
    public function metagridComponentProcessesStdClassWithoutTypeErrors(): void
    {
        $this->markTestSkipped('MetagridLwComponent requires Laravel facades - test in integration environment');
    }

    /**
     * Test that all provider components handle their expected data structures correctly.
     * This is a comprehensive test that would catch type mismatches across all providers.
     */
    #[Test]
    #[DataProvider('allProvidersDataProvider')]
    public function allProvidersProcessDataWithoutTypeErrors(string $componentClass, $mockData, array $expectedFields): void
    {
        // Skip components that require Laravel facades in standalone tests
        $facadeComponents = [
            MetagridLwComponent::class,
            AntonLwComponent::class, // Might use facades
        ];
        
        if (in_array($componentClass, $facadeComponents)) {
            $this->markTestSkipped("Component {$componentClass} requires Laravel facades - test in integration environment");
        }
        
        $component = new $componentClass();
        
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        // This should not throw any TypeError or Exception
        $results = $processMethod->invoke($component, $mockData);
        
        // Basic validation
        $this->assertIsArray($results);
        
        if (!empty($results)) {
            $result = $results[0];
            
            // Check that expected fields exist and are accessible as array elements
            foreach ($expectedFields as $field) {
                $this->assertArrayHasKey($field, $result, "Field '{$field}' should exist in {$componentClass} result");
            }
            
            // Test JSON encoding (critical for Livewire wire:click)
            $jsonResult = json_encode($result);
            $this->assertNotFalse($jsonResult, "Result from {$componentClass} should be JSON encodable");
        }
    }

    /**
     * Test that all components handle empty responses gracefully.
     */
    #[Test]
    public function allComponentsHandleEmptyResponsesGracefully(): void
    {
        $components = [
            GndLwComponent::class,
            WikidataLwComponent::class,
            WikipediaLwComponent::class,
            GeonamesLwComponent::class,
            IdiotikonLwComponent::class,
            OrtsnamenLwComponent::class,
            // Skip facade-dependent components
        ];

        foreach ($components as $componentClass) {
            $component = new $componentClass();
            $reflection = new ReflectionClass($component);
            $processMethod = $reflection->getMethod('processResults');
            $processMethod->setAccessible(true);

            // Test with null - should not throw exceptions
            $result = $processMethod->invoke($component, null);
            $this->assertIsArray($result, "Component {$componentClass} should return array for null input");
            
            // Test with empty object - should not throw exceptions  
            $result = $processMethod->invoke($component, (object) []);
            $this->assertIsArray($result, "Component {$componentClass} should return array for empty object");
            
            // Test with empty array - should not throw exceptions
            $result = $processMethod->invoke($component, []);
            $this->assertIsArray($result, "Component {$componentClass} should return array for empty array");
        }
    }

    /**
     * Data provider for all provider components with their expected mock data and fields.
     */
    public static function allProvidersDataProvider(): array
    {
        return [
            'GND Component' => [
                GndLwComponent::class,
                (object) [
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
                ],
                ['gndIdentifier', 'preferredName', 'dateOfBirth', 'dateOfDeath', 'biographicalOrHistoricalInformation']
            ],
            
            'Wikidata Component' => [
                WikidataLwComponent::class,
                (object) [
                    'search' => [
                        (object) [
                            'id' => 'Q123456',
                            'label' => 'Test Entity',
                            'description' => 'Test description',
                            'url' => 'https://www.wikidata.org/wiki/Q123456'
                        ]
                    ]
                ],
                ['provider_id', 'preferredName', 'description', 'url']
            ],
            
            'Wikipedia Component' => [
                WikipediaLwComponent::class,
                (object) [
                    'query' => (object) [
                        'search' => [
                            (object) [
                                'title' => 'Test Article',
                                'snippet' => 'Test snippet',
                                'size' => 1000,
                                'wordcount' => 200,
                                'timestamp' => '2023-01-01T00:00:00Z'
                            ]
                        ]
                    ]
                ],
                ['provider_id', 'preferredName', 'description', 'url']
            ],
            
            'Geonames Component' => [
                GeonamesLwComponent::class,
                (object) [
                    'geonames' => [
                        (object) [
                            'geonameId' => 123456,
                            'name' => 'Test Place',
                            'countryName' => 'Test Country',
                            'adminName1' => 'Test Admin',
                            'lat' => '47.0',
                            'lng' => '8.0'
                        ]
                    ]
                ],
                ['provider_id', 'preferredName', 'countryName', 'lat', 'lng', 'url']
            ],
            
            'Idiotikon Component' => [
                IdiotikonLwComponent::class,
                [
                    (object) [
                        'id' => 'test_123',
                        'lemma' => 'Test Lemma',
                        'definition' => 'Test definition',
                        'volume' => 'I',
                        'column' => '123'
                    ]
                ],
                ['provider_id', 'preferredName', 'definition', 'volume', 'column', 'url']
            ],
            
            'Ortsnamen Component' => [
                OrtsnamenLwComponent::class,
                [
                    (object) [
                        'id' => 'on_123',
                        'name' => 'Test Ortname',
                        'municipality' => 'Test Municipality',
                        'canton' => 'Test Canton',
                        'coordinates' => (object) [
                            'lat' => 47.0,
                            'lng' => 8.0
                        ]
                    ]
                ],
                ['provider_id', 'preferredName', 'municipality', 'canton', 'url']
            ],
        ];
    }    /**
     * Test that array access patterns used in views work without exceptions.
     */
    #[Test]
    public function viewCompatibleArrayAccessWorks(): void
    {
        $component = new GndLwComponent();
        
        $mockGndResponse = (object) [
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
        ];
        
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        $results = $processMethod->invoke($component, $mockGndResponse);
        $result = $results[0];
        
        // Test patterns commonly used in views - these would throw exceptions if types were wrong
        $gndId = $result['gndIdentifier'] ?? null;
        $birthDate = $result['dateOfBirth'][0] ?? null;
        $deathDate = $result['dateOfDeath'][0] ?? null;
        $bio = $result['biographicalOrHistoricalInformation'][0] ?? null;
        
        $this->assertNotNull($gndId);
        $this->assertNotNull($birthDate);
        $this->assertNotNull($deathDate);
        $this->assertNotNull($bio);
    }

    /**
     * Test that components produce data compatible with wire:click patterns.
     */
    #[Test]
    public function wireClickCompatibilityWorks(): void
    {
        $component = new GndLwComponent();
        
        $mockGndResponse = (object) [
            'totalItems' => 1,
            'member' => [
                (object) [
                    'preferredName' => 'Test Person',
                    'gndIdentifier' => '123456789',
                    'type' => ['Person']
                ]
            ]
        ];
        
        $reflection = new ReflectionClass($component);
        $processMethod = $reflection->getMethod('processResults');
        $processMethod->setAccessible(true);
        
        $results = $processMethod->invoke($component, $mockGndResponse);
        $result = $results[0];
        
        // Test that the result can be passed to wire:click as JSON
        $wireClickData = [
            'provider' => 'gnd',
            'data' => $result
        ];
        
        $jsonEncoded = json_encode($wireClickData);
        $this->assertNotFalse($jsonEncoded);
        
        // Test that it can be decoded back
        $decoded = json_decode($jsonEncoded, true);
        $this->assertNotNull($decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('gndIdentifier', $decoded['data']);
    }
}
