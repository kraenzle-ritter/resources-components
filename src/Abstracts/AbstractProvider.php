<?php

namespace KraenzleRitter\ResourcesComponents\Abstracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use KraenzleRitter\ResourcesComponents\Services\CacheService;

abstract class AbstractProvider implements ProviderInterface
{
    protected Client $client;
    protected string $baseUrl;
    protected array $defaultParams = [];
    protected int $defaultLimit = 5;
    protected CacheService $cacheService;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => $this->getBaseUrl()]);
        $this->cacheService = new CacheService();
        $this->setDefaultParams();
    }

    /**
     * Set default parameters for the provider
     */
    protected function setDefaultParams(): void
    {
        $this->defaultParams = [
            'limit' => $this->getConfigValue('limit', $this->defaultLimit)
        ];
    }

    /**
     * Get configuration value for this provider
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfigValue(string $key, $default = null)
    {
        $providerKey = strtolower($this->getProviderName());
        return config("resources-components.{$providerKey}.{$key}", $default);
    }

    /**
     * Make HTTP request with error handling
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return mixed
     */
    protected function makeRequest(string $method, string $uri, array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents());
            }
        } catch (RequestException $e) {
            Log::error("Error in {$this->getProviderName()} provider", [
                'method' => $method,
                'uri' => $uri,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Sanitize search string
     *
     * @param string $search
     * @return string
     */
    protected function sanitizeSearch(string $search): string
    {
        return trim(str_replace(['[', ']', '!', '(', ')', ':'], ' ', $search));
    }

    /**
     * Merge parameters with defaults
     *
     * @param array $params
     * @return array
     */
    protected function mergeParams(array $params): array
    {
        return array_merge($this->defaultParams, $params);
    }

    /**
     * Search with caching support
     *
     * @param string $search
     * @param array $params
     * @return mixed
     */
    public function searchWithCache(string $search, array $params = [])
    {
        return $this->cacheService->remember($this, $search, $params);
    }

    /**
     * Clear cache for this provider
     *
     * @param string|null $search
     * @param array $params
     */
    public function clearCache(string $search = null, array $params = []): void
    {
        $this->cacheService->forget($this, $search, $params);
    }

    /**
     * Get the base URL for this provider
     */
    abstract public function getBaseUrl(): string;

    /**
     * Get the provider's name
     */
    abstract public function getProviderName(): string;
}
