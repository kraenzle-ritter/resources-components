<?php

namespace KraenzleRitter\ResourcesComponents\Contracts;

interface ProviderInterface
{
    /**
     * Search for resources using the provider's API
     *
     * @param string $search The search query
     * @param array $params Additional search parameters
     * @return mixed The search results
     */
    public function search(string $search, array $params = []);

    /**
     * Get the provider's name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Get the base URL for this provider
     *
     * @return string
     */
    public function getBaseUrl(): string;
}
