<?php

namespace KraenzleRitter\ResourcesComponents\Providers;

use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * The base URL for the provider API
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The provider key (identifier)
     *
     * @var string
     */
    protected $providerKey;

    /**
     * The human-readable label for the provider
     *
     * @var string
     */
    protected $label;

    /**
     * Additional configuration options
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new provider instance
     *
     * @param string $providerKey The provider key from config
     * @param array $config The provider configuration
     */
    public function __construct(string $providerKey, array $config = [])
    {
        $this->providerKey = $providerKey;
        $this->config = $config;

        $this->baseUrl = $config['base_url'] ?? '';
        $this->label = $config['label'] ?? ucfirst($providerKey);
    }

    /**
     * Get the base URL for the provider
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the provider key (identifier)
     *
     * @return string
     */
    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    /**
     * Get the human-readable label for the provider
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get a specific configuration value
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check if the provider has a specific configuration
     *
     * @param string $key The configuration key
     * @return bool
     */
    protected function hasConfig(string $key): bool
    {
        return isset($this->config[$key]) && !empty($this->config[$key]);
    }
}
