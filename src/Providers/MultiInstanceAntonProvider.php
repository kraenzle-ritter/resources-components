<?php

namespace KraenzleRitter\ResourcesComponents\Providers;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;
use KraenzleRitter\ResourcesComponents\Helpers\Params;
use Illuminate\Support\Facades\Log;

/**
 * Multi-Instance Anton Provider
 * 
 * Supports multiple Anton implementations with different base URLs
 * Each instance can have its own API URL, token, and configuration
 */
class MultiInstanceAntonProvider extends AbstractProvider
{
    protected array $instances = [];
    protected string $currentInstance = 'default';

    public function __construct()
    {
        parent::__construct();
        $this->loadInstances();
    }

    public function getBaseUrl(): string
    {
        return $this->getCurrentInstanceConfig('api_url', '');
    }

    public function getProviderName(): string
    {
        return 'MultiInstanceAnton';
    }

    /**
     * Load configured Anton instances from config
     */
    protected function loadInstances(): void
    {
        $this->instances = config('resources-components.anton.instances', [
            'default' => [
                'name' => 'Default Anton',
                'api_url' => config('resources-components.anton.api_url', ''),
                'token' => config('resources-components.anton.token', ''),
                'limit' => 5,
                'enabled' => true
            ]
        ]);
    }

    /**
     * Set the current Anton instance to use
     */
    public function setInstance(string $instanceName): self
    {
        if ($this->hasInstance($instanceName)) {
            $this->currentInstance = $instanceName;
        }
        return $this;
    }

    /**
     * Get current instance name
     */
    public function getCurrentInstance(): string
    {
        return $this->currentInstance;
    }

    /**
     * Check if instance exists and is enabled
     */
    public function hasInstance(string $instanceName): bool
    {
        return isset($this->instances[$instanceName]) && 
               ($this->instances[$instanceName]['enabled'] ?? true);
    }

    /**
     * Get configuration value for current instance
     */
    protected function getCurrentInstanceConfig(string $key, $default = null)
    {
        return $this->instances[$this->currentInstance][$key] ?? $default;
    }

    /**
     * Get current instance token
     */
    protected function getCurrentToken(): string
    {
        return $this->getCurrentInstanceConfig('token', '');
    }

    /**
     * Search in current Anton instance
     */
    public function search(string $search, array $params = [], string $endpoint = 'objects'): array
    {
        $token = $this->getCurrentToken();
        
        if (!$search || !$token) {
            return [];
        }

        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        // Handle pagination and limits
        $queryParams = $params;
        $queryParams['perPage'] = $params['size'] ?? $this->getCurrentInstanceConfig('limit', 5);
        $queryParams['page'] = $params['page'] ?? 1;
        unset($queryParams['size']);

        // Add authentication and search parameters
        $queryParams = array_merge([
            '?search' => $search, 
            'api_token' => $token
        ], $queryParams);
        
        $queryString = Params::toQueryString($queryParams);

        // Create endpoint-specific client for current instance
        $baseUrl = $this->getCurrentInstanceConfig('api_url', '');
        $endpointUrl = rtrim($baseUrl, '/') . '/' . $endpoint . '/';
        
        $endpointClient = new \GuzzleHttp\Client(['base_uri' => $endpointUrl]);

        try {
            $response = $endpointClient->get($queryString);
            $result = json_decode($response->getBody()->getContents());
            
            // Add instance information to results
            if (isset($result->data)) {
                foreach ($result->data as $item) {
                    $item->anton_instance = $this->currentInstance;
                    $item->anton_instance_name = $this->getCurrentInstanceConfig('name', $this->currentInstance);
                }
            }
            
            return $result->data ?? [];
        } catch (\Exception $e) {
            Log::error("Anton search error (instance: {$this->currentInstance}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search across multiple Anton instances
     */
    public function searchAllInstances(string $search, array $params = [], string $endpoint = 'objects'): array
    {
        $allResults = [];
        $originalInstance = $this->currentInstance;

        foreach ($this->getAvailableInstances() as $instanceName) {
            $this->setInstance($instanceName);
            $results = $this->search($search, $params, $endpoint);
            $allResults = array_merge($allResults, $results);
        }

        // Restore original instance
        $this->setInstance($originalInstance);
        
        return $allResults;
    }

    /**
     * Search in specific Anton instance
     */
    public function searchInInstance(string $instanceName, string $search, array $params = [], string $endpoint = 'objects'): array
    {
        $originalInstance = $this->currentInstance;
        $this->setInstance($instanceName);
        
        $results = $this->search($search, $params, $endpoint);
        
        // Restore original instance
        $this->setInstance($originalInstance);
        
        return $results;
    }

    /**
     * Get list of available (enabled) instances
     */
    public function getAvailableInstances(): array
    {
        return array_keys(array_filter($this->instances, function($instance) {
            return $instance['enabled'] ?? true;
        }));
    }

    /**
     * Get all instances with their configuration
     */
    public function getAllInstances(): array
    {
        return $this->instances;
    }

    /**
     * Get instance configuration
     */
    public function getInstanceConfig(string $instanceName): ?array
    {
        return $this->instances[$instanceName] ?? null;
    }

    /**
     * Legacy method for backward compatibility - works with current instance
     */
    public function getPlaceByGeonameId(string $id): ?\SimpleXMLElement
    {
        if (!$id) {
            return null;
        }

        // Build separate client for Geonames API
        $geonamesClient = new \GuzzleHttp\Client(['base_uri' => 'http://api.geonames.org/']);

        try {
            $response = $geonamesClient->get('get?geonameId=' . $id . '&username=antonatgeonames');
            $xml = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
            return $xml;
        } catch (\Exception $e) {
            Log::error("Anton getPlaceByGeonameId error (instance: {$this->currentInstance}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add new instance configuration (runtime)
     */
    public function addInstance(string $name, array $config): self
    {
        $this->instances[$name] = array_merge([
            'name' => $name,
            'api_url' => '',
            'token' => '',
            'limit' => 5,
            'enabled' => true
        ], $config);
        
        return $this;
    }

    /**
     * Enable/disable instance
     */
    public function toggleInstance(string $instanceName, bool $enabled): self
    {
        if (isset($this->instances[$instanceName])) {
            $this->instances[$instanceName]['enabled'] = $enabled;
        }
        
        return $this;
    }
}
