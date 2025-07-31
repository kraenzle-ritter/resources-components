<?php

namespace KraenzleRitter\ResourcesComponents;

class ResourcesComponents
{
    /**
     * Checks if a provider configuration exists
     *
     * @param string $provider
     * @return bool
     */
    public function hasProvider(string $provider): bool
    {
        return config("resources-components.providers.{$provider}") !== null;
    }

    /**
     * Gibt die Liste der verfügbaren Provider zurück
     *
     * @return array
     */
    public function getProviders(): array
    {
        return config('resources-components.providers', []);
    }
}
