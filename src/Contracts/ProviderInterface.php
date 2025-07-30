<?php

namespace KraenzleRitter\ResourcesComponents\Contracts;

interface ProviderInterface
{
    /**
     * Search for resources at the provider
     *
     * @param string $search The search query string
     * @param array $params Additional parameters for the search
     * @return mixed The search results that will be processed
     */
    public function search(string $search, array $params = []);

    /**
     * Process the search results into a standardized format
     *
     * @param mixed $results The results from the search method
     * @return array Standardized array of results for display and saving
     */
    public function processResult($results): array;

    /**
     * Get the base URL for the provider
     *
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * Get the provider key (identifier)
     *
     * @return string
     */
    public function getProviderKey(): string;

    /**
     * Get the human-readable label for the provider
     *
     * @return string
     */
    public function getLabel(): string;
}
