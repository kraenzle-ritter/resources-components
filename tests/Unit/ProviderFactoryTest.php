<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use InvalidArgumentException;

class ProviderFactoryTest extends TestCase
{
    /** @test */
    public function it_can_create_gnd_provider()
    {
        $provider = ProviderFactory::create('gnd');
        
        $this->assertInstanceOf(Gnd::class, $provider);
        $this->assertInstanceOf(ProviderInterface::class, $provider);
    }

    /** @test */
    public function it_throws_exception_for_unknown_provider()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Provider 'unknown' is not supported.");
        
        ProviderFactory::create('unknown');
    }

    /** @test */
    public function it_can_register_new_provider()
    {
        // Create a mock provider class
        $mockProviderClass = new class implements ProviderInterface {
            public function search(string $search, array $params = []) { return []; }
            public function getProviderName(): string { return 'Test'; }
            public function getBaseUrl(): string { return 'https://test.com'; }
        };

        $className = get_class($mockProviderClass);
        ProviderFactory::register('test', $className);
        
        $this->assertTrue(ProviderFactory::isAvailable('test'));
        $this->assertContains('test', ProviderFactory::getAvailableProviders());
    }

    /** @test */
    public function it_can_check_if_provider_is_available()
    {
        $this->assertTrue(ProviderFactory::isAvailable('gnd'));
        $this->assertFalse(ProviderFactory::isAvailable('nonexistent'));
    }

    /** @test */
    public function it_returns_all_available_providers()
    {
        $providers = ProviderFactory::getAvailableProviders();
        
        $this->assertIsArray($providers);
        $this->assertContains('gnd', $providers);
        $this->assertContains('wikidata', $providers);
    }
}
