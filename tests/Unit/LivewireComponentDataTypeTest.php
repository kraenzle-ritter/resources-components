<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\GndLwComponent;
use KraenzleRitter\ResourcesComponents\MetagridLwComponent;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

class LivewireComponentDataTypeTest extends TestCase
{
    /**
     * Test that GND component handles object-to-array conversion without throwing exceptions.
     * This would have caught the original "Attempt to read property on array" bug.
     */
    #[Test]
    public function gndComponentProcessesWithoutTypeErrors(): void
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
        
        // Basic validation using compatible assertions
        $this->assertTrue(is_array($results));
        $this->assertTrue(!empty($results));
        
        $result = $results[0];
        
        // These fields should exist and be accessible as array elements
        $this->assertTrue(array_key_exists('gndIdentifier', $result));
        $this->assertTrue(array_key_exists('dateOfBirth', $result));
        $this->assertTrue(array_key_exists('dateOfDeath', $result));
        $this->assertTrue(array_key_exists('biographicalOrHistoricalInformation', $result));
        
        // Test JSON encoding (critical for Livewire wire:click)
        $jsonResult = json_encode($result);
        $this->assertTrue($jsonResult !== false);
    }

    /**
     * Test that Metagrid component handles stdClass objects without throwing exceptions.
     * This would have caught the original "Cannot use object of type stdClass as array" bug.
     */
    #[Test]
    public function metagridComponentProcessesWithoutTypeErrors(): void
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
        
        // This should not throw any TypeError or Exception
        $results = $processMethod->invoke($component, $mockMetagridResponse);
        
        // If we reach this point without exceptions, test passes
    }

    /**
     * Test that components handle empty responses gracefully.
     */
    public function testComponentsHandleEmptyResponsesGracefully()
    {
        $components = [
            GndLwComponent::class,
            MetagridLwComponent::class,
        ];

        foreach ($components as $componentClass) {
            $component = new $componentClass();
            $reflection = new ReflectionClass($component);
            $processMethod = $reflection->getMethod('processResults');
            $processMethod->setAccessible(true);

            // Test with null - should not throw exceptions
            $processMethod->invoke($component, null);
            
            // Test with empty object - should not throw exceptions
            $processMethod->invoke($component, (object) []);
            
            // Test with empty array - should not throw exceptions
            $processMethod->invoke($component, []);
        }
        
        // If we reach this point without exceptions, test passes
    }

    /**
     * Test that array access patterns used in views work without exceptions.
     */
    public function testViewCompatibleArrayAccessWorks()
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
        
        // If we reach this point without exceptions, test passes
    }

    /**
     * Test that components produce data compatible with wire:click patterns.
     */
    public function testWireClickCompatibilityWorks()
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
        $decoded = json_decode($jsonEncoded, true);
        
        // If we reach this point without exceptions, test passes
    }
}
