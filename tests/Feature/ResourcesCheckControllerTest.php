<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Http\Controllers\ResourcesCheckController;
use Illuminate\Support\Facades\Config;

class ResourcesCheckControllerTest extends TestCase
{
    /**
     * Test that the controller uses test_search from provider configuration
     */
    public function test_controller_uses_test_search_from_config()
    {
        // Set up test configuration
        Config::set('resources-components.providers.test-provider', [
            'label' => 'Test Provider',
            'api-type' => 'TestType',
            'test_search' => 'Custom Test Query'
        ]);

        $controller = new ResourcesCheckController();

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getTestQuery');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'test-provider');

        $this->assertEquals('Custom Test Query', $result);
    }

    /**
     * Test fallback when test_search is not configured
     */
    public function test_controller_falls_back_to_default()
    {
        // Set up configuration without test_search
        Config::set('resources-components.providers.test-provider-2', [
            'label' => 'Test Provider 2',
            'api-type' => 'TestType'
            // No test_search configured
        ]);

        $controller = new ResourcesCheckController();

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getTestQuery');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'test-provider-2');

        $this->assertEquals('test', $result);
    }

    /**
     * Test behavior with non-existent provider
     */
    public function test_controller_handles_non_existent_provider()
    {
        $controller = new ResourcesCheckController();

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getTestQuery');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'non-existent-provider');

        $this->assertEquals('test', $result);
    }

    /**
     * Test that real providers from config have test_search configured
     */
    public function test_real_providers_have_test_search()
    {
        $providers = Config::get('resources-components.providers');

        foreach ($providers as $key => $config) {
            // Skip manual-input as it doesn't need test_search
            if ($key === 'manual-input') {
                continue;
            }

            $this->assertArrayHasKey('test_search', $config,
                "Provider '{$key}' should have 'test_search' configured");
            $this->assertNotEmpty($config['test_search'],
                "Provider '{$key}' should have a non-empty 'test_search' value");
        }
    }
}
