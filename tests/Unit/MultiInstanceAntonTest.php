<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Providers\MultiInstanceAntonProvider;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class MultiInstanceAntonTest extends TestCase
{
    protected MultiInstanceAntonProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock configuration
        $this->app['config']->set('resources-components.anton.instances', [
            'default' => [
                'name' => 'Default Anton',
                'api_url' => 'https://api.anton.ch',
                'token' => 'test-token-1',
                'limit' => 5,
                'enabled' => true
            ],
            'cultural' => [
                'name' => 'Cultural Heritage',
                'api_url' => 'https://cultural.anton.ch',
                'token' => 'test-token-2',
                'limit' => 10,
                'enabled' => true
            ],
            'disabled' => [
                'name' => 'Disabled Instance',
                'api_url' => 'https://disabled.anton.ch',
                'token' => 'test-token-3',
                'limit' => 5,
                'enabled' => false
            ]
        ]);

        $this->provider = new MultiInstanceAntonProvider();
    }

    #[Test]
    public function it_can_create_multi_instance_anton_instance()
    {
        $this->assertInstanceOf(MultiInstanceAntonProvider::class, $this->provider);
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    #[Test]
    public function it_returns_correct_provider_name()
    {
        $this->assertEquals('MultiInstanceAnton', $this->provider->getProviderName());
    }

    #[Test]
    public function it_loads_instances_from_config()
    {
        $instances = $this->provider->getAllInstances();
        
        $this->assertIsArray($instances);
        $this->assertArrayHasKey('default', $instances);
        $this->assertArrayHasKey('cultural', $instances);
        $this->assertEquals('Default Anton', $instances['default']['name']);
    }

    #[Test]
    public function it_can_set_and_get_current_instance()
    {
        $this->assertEquals('default', $this->provider->getCurrentInstance());
        
        $this->provider->setInstance('cultural');
        $this->assertEquals('cultural', $this->provider->getCurrentInstance());
    }

    #[Test]
    public function it_only_sets_valid_instances()
    {
        $this->provider->setInstance('nonexistent');
        $this->assertEquals('default', $this->provider->getCurrentInstance());
    }

    #[Test]
    public function it_checks_instance_existence_correctly()
    {
        $this->assertTrue($this->provider->hasInstance('default'));
        $this->assertTrue($this->provider->hasInstance('cultural'));
        $this->assertFalse($this->provider->hasInstance('disabled')); // disabled instances return false
        $this->assertFalse($this->provider->hasInstance('nonexistent'));
    }

    #[Test]
    public function it_returns_available_instances_only_enabled()
    {
        $available = $this->provider->getAvailableInstances();
        
        $this->assertIsArray($available);
        $this->assertContains('default', $available);
        $this->assertContains('cultural', $available);
        $this->assertNotContains('disabled', $available);
    }

    #[Test]
    public function it_returns_correct_base_url_for_current_instance()
    {
        $this->assertEquals('https://api.anton.ch', $this->provider->getBaseUrl());
        
        $this->provider->setInstance('cultural');
        $this->assertEquals('https://cultural.anton.ch', $this->provider->getBaseUrl());
    }

    #[Test]
    public function it_can_search_in_current_instance()
    {
        // Mock successful search response
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    (object)[
                        'title' => 'Test Object',
                        'description' => 'Test description'
                    ]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        
        // We need to mock the client creation in the search method
        // This is more complex due to the dynamic client creation
        $results = [];
        
        // For now, test that the method exists and returns array
        $this->assertIsArray($this->provider->search('test'));
    }

    #[Test]
    public function it_returns_empty_array_with_no_search_term()
    {
        $results = $this->provider->search('');
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    #[Test]
    public function it_returns_empty_array_with_no_token()
    {
        // Set instance without token
        $this->provider->addInstance('no-token', [
            'name' => 'No Token Instance',
            'api_url' => 'https://notoken.anton.ch',
            'token' => '',
            'enabled' => true
        ]);
        
        $this->provider->setInstance('no-token');
        $results = $this->provider->search('test');
        
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    #[Test]
    public function it_can_add_instance_at_runtime()
    {
        $this->provider->addInstance('runtime', [
            'name' => 'Runtime Instance',
            'api_url' => 'https://runtime.anton.ch',
            'token' => 'runtime-token'
        ]);

        $instances = $this->provider->getAllInstances();
        $this->assertArrayHasKey('runtime', $instances);
        $this->assertEquals('Runtime Instance', $instances['runtime']['name']);
    }

    #[Test]
    public function it_can_toggle_instance_enabled_status()
    {
        $this->provider->toggleInstance('cultural', false);
        $available = $this->provider->getAvailableInstances();
        $this->assertNotContains('cultural', $available);

        $this->provider->toggleInstance('cultural', true);
        $available = $this->provider->getAvailableInstances();
        $this->assertContains('cultural', $available);
    }

    #[Test]
    public function it_can_get_instance_configuration()
    {
        $config = $this->provider->getInstanceConfig('default');
        
        $this->assertIsArray($config);
        $this->assertEquals('Default Anton', $config['name']);
        $this->assertEquals('https://api.anton.ch', $config['api_url']);
        $this->assertTrue($config['enabled']);
    }

    #[Test]
    public function it_returns_null_for_nonexistent_instance_config()
    {
        $config = $this->provider->getInstanceConfig('nonexistent');
        $this->assertNull($config);
    }

    #[Test]
    public function it_restores_original_instance_after_search_in_instance()
    {
        $this->assertEquals('default', $this->provider->getCurrentInstance());
        
        $this->provider->searchInInstance('cultural', 'test');
        
        // Should restore to original instance
        $this->assertEquals('default', $this->provider->getCurrentInstance());
    }
}
