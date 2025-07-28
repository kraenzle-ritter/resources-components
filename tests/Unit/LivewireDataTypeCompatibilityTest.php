<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\GndLwComponent;
use KraenzleRitter\ResourcesComponents\MetagridLwComponent;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * Data type compatibility tests for Livewire components using Orchestra Testbench.
 * These tests would have caught the original type-related bugs.
 */
class LivewireDataTypeCompatibilityTest extends TestCase
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
        
        // Basic validation - if we reach this point, no exceptions were thrown
        $this->assertTrue(is_array($results));
        $this->assertTrue(!empty($results));
        
        $result = $results[0];
        
        // These field accesses would throw exceptions if data types were wrong
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
    public function metagridComponentProcessesStdClassWithoutTypeErrors(): void
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
        
        // Basic validation - if we reach this point, no exceptions were thrown
        $this->assertTrue(is_array($results));
        $this->assertTrue(!empty($results));
        
        $result = $results[0];
        
        // These field accesses would throw exceptions if data types were wrong
        $this->assertTrue(array_key_exists('provider_id', $result));
        $this->assertTrue(array_key_exists('url', $result));
        $this->assertTrue(array_key_exists('preferredName', $result));
        
        // Test JSON encoding
        $jsonResult = json_encode($result);
        $this->assertTrue($jsonResult !== false);
    }

    /**
     * Test that components handle empty responses gracefully.
     */
    #[Test]
    public function componentsHandleEmptyResponsesGracefully(): void
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
            $result = $processMethod->invoke($component, null);
            $this->assertTrue(is_array($result), "Component {$componentClass} should return array for null input");
            
            // Test with empty object - should not throw exceptions  
            $result = $processMethod->invoke($component, (object) []);
            $this->assertTrue(is_array($result), "Component {$componentClass} should return array for empty object");
            
            // Test with empty array - should not throw exceptions
            $result = $processMethod->invoke($component, []);
            $this->assertTrue(is_array($result), "Component {$componentClass} should return array for empty array");
        }
    }

    /**
     * Test that array access patterns used in views work without exceptions.
     * This simulates what happens in the Blade templates.
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
        
        $this->assertTrue($gndId !== null);
        $this->assertTrue($birthDate !== null);
        $this->assertTrue($deathDate !== null);
        $this->assertTrue($bio !== null);
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
        $this->assertTrue($jsonEncoded !== false);
        
        // Test that it can be decoded back
        $decoded = json_decode($jsonEncoded, true);
        $this->assertTrue($decoded !== null);
        $this->assertTrue(array_key_exists('data', $decoded));
        $this->assertTrue(array_key_exists('gndIdentifier', $decoded['data']));
    }
}
