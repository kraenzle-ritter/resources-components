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
        // Override in child classes if needed
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
     * Recursively convert arrays to objects while preserving indexed arrays
     *
     * @param mixed $data
     * @return mixed
     */
    protected function arrayToObject($data)
    {
        if (is_array($data)) {
            // Check if it's an associative array
            if (array_keys($data) !== range(0, count($data) - 1)) {
                // Associative array - convert to object
                return (object) array_map([$this, 'arrayToObject'], $data);
            } else {
                // Indexed array - keep as array but convert elements
                return array_map([$this, 'arrayToObject'], $data);
            }
        }
        return $data;
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
