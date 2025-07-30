<?php

namespace KraenzleRitter\ResourcesComponents;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use KraenzleRitter\ResourcesComponents\Providers\WikipediaProvider;
use KraenzleRitter\ResourcesComponents\Exceptions\ProviderNotFoundException;

class ProviderFactory
{
    /**
     * The application instance
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Map of provider keys to provider classes
     *
     * @var array
     */
    protected $providerMap = [
        'wikipedia' => WikipediaProvider::class,
        'wikipedia-de' => WikipediaProvider::class,
        'wikipedia-en' => WikipediaProvider::class,
        'wikipedia-fr' => WikipediaProvider::class,
        'wikipedia-it' => WikipediaProvider::class,
    ];

    /**
     * Create a new provider factory
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Create a provider instance
     *
     * @param string $providerKey The provider key from config
     * @return ProviderInterface
     * @throws ProviderNotFoundException
     */
    public function make(string $providerKey): ProviderInterface
    {
        // Normalize provider key
        $providerKey = strtolower($providerKey);

        // Get provider config
        $config = Config::get("resources-components.providers.{$providerKey}", []);

        // Get provider class from map or config
        $providerClass = $this->providerMap[$providerKey] ?? null;

        // If not in map, try to get from config
        if (!$providerClass && isset($config['provider_class'])) {
            $providerClass = $config['provider_class'];
        }

        // If still not found, throw exception
        if (!$providerClass) {
            throw new ProviderNotFoundException("Provider not found for key: {$providerKey}");
        }

        // Create provider instance
        return new $providerClass($providerKey, $config);
    }

    /**
     * Register a custom provider class for a provider key
     *
     * @param string $providerKey The provider key
     * @param string $providerClass The provider class name
     * @return void
     */
    public function extend(string $providerKey, string $providerClass): void
    {
        $this->providerMap[strtolower($providerKey)] = $providerClass;
    }

    /**
     * Get all available providers from config
     *
     * @return array
     */
    public function getAvailableProviders(): array
    {
        $providers = [];
        $config = Config::get('resources-components.providers', []);

        foreach ($config as $key => $providerConfig) {
            $providers[$key] = $providerConfig['label'] ?? ucfirst($key);
        }

        return $providers;
    }
}
