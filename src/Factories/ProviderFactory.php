<?php

namespace KraenzleRitter\ResourcesComponents\Factories;

use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Geonames;
use KraenzleRitter\ResourcesComponents\Metagrid;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Ortsnamen;
use KraenzleRitter\ResourcesComponents\Anton;
use InvalidArgumentException;

class ProviderFactory
{
    /**
     * Available providers
     *
     * @var array
     */
    protected static array $providers = [
        'gnd' => Gnd::class,
        'wikidata' => Wikidata::class,
        'wikipedia' => Wikipedia::class,
        'geonames' => Geonames::class,
        'metagrid' => Metagrid::class,
        'idiotikon' => Idiotikon::class,
        'ortsnamen' => Ortsnamen::class,
        'anton' => Anton::class,
    ];

    /**
     * Create a provider instance
     *
     * @param string $provider
     * @return ProviderInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $provider): ProviderInterface
    {
        $provider = strtolower($provider);

        if (!isset(static::$providers[$provider])) {
            throw new InvalidArgumentException("Provider '{$provider}' is not supported.");
        }

        $providerClass = static::$providers[$provider];

        return new $providerClass();
    }

    /**
     * Register a new provider
     *
     * @param string $name
     * @param string $class
     * @throws InvalidArgumentException
     */
    public static function register(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Provider class '{$class}' does not exist.");
        }

        if (!in_array(ProviderInterface::class, class_implements($class))) {
            throw new InvalidArgumentException("Provider class '{$class}' must implement ProviderInterface.");
        }

        static::$providers[strtolower($name)] = $class;
    }

    /**
     * Get all available providers
     *
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(static::$providers);
    }

    /**
     * Check if a provider is available
     *
     * @param string $provider
     * @return bool
     */
    public static function isAvailable(string $provider): bool
    {
        return isset(static::$providers[strtolower($provider)]);
    }
}
