<?php

namespace KraenzleRitter\ResourcesComponents\Services;

use Illuminate\Support\Facades\Cache;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;

class CacheService
{
    protected int $ttl;
    protected string $prefix;
    protected bool $enabled;

    public function __construct()
    {
        $this->ttl = config('resources-components.cache.ttl', 3600);
        $this->prefix = config('resources-components.cache.prefix', 'resources_components');
        $this->enabled = config('resources-components.cache.enabled', true);
    }

    /**
     * Get cached results or execute the search and cache the results
     *
     * @param ProviderInterface $provider
     * @param string $search
     * @param array $params
     * @return mixed
     */
    public function remember(ProviderInterface $provider, string $search, array $params = [])
    {
        if (!$this->enabled) {
            return $provider->search($search, $params);
        }

        $cacheKey = $this->generateCacheKey($provider, $search, $params);

        return Cache::remember($cacheKey, $this->ttl, function () use ($provider, $search, $params) {
            return $provider->search($search, $params);
        });
    }

    /**
     * Clear cache for a specific provider
     *
     * @param ProviderInterface $provider
     * @param string|null $search
     * @param array $params
     */
    public function forget(ProviderInterface $provider, string $search = null, array $params = []): void
    {
        if ($search !== null) {
            $cacheKey = $this->generateCacheKey($provider, $search, $params);
            Cache::forget($cacheKey);
        } else {
            // Clear all cache for this provider
            $this->clearProviderCache($provider);
        }
    }

    /**
     * Clear all cache for resources components
     */
    public function flush(): void
    {
        $keys = Cache::get($this->prefix . '_keys', []);
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Cache::forget($this->prefix . '_keys');
    }

    /**
     * Generate cache key for provider search
     *
     * @param ProviderInterface $provider
     * @param string $search
     * @param array $params
     * @return string
     */
    protected function generateCacheKey(ProviderInterface $provider, string $search, array $params): string
    {
        $providerName = strtolower($provider->getProviderName());
        $searchHash = md5($search . serialize($params));
        
        $key = "{$this->prefix}:{$providerName}:{$searchHash}";
        
        // Track cache keys for easier cleanup
        $this->trackCacheKey($key);
        
        return $key;
    }

    /**
     * Track cache keys for easier cleanup
     *
     * @param string $key
     */
    protected function trackCacheKey(string $key): void
    {
        $keys = Cache::get($this->prefix . '_keys', []);
        
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::put($this->prefix . '_keys', $keys, $this->ttl);
        }
    }

    /**
     * Clear all cache for a specific provider
     *
     * @param ProviderInterface $provider
     */
    protected function clearProviderCache(ProviderInterface $provider): void
    {
        $providerName = strtolower($provider->getProviderName());
        $keys = Cache::get($this->prefix . '_keys', []);
        
        $providerKeys = array_filter($keys, function ($key) use ($providerName) {
            return strpos($key, "{$this->prefix}:{$providerName}:") === 0;
        });
        
        foreach ($providerKeys as $key) {
            Cache::forget($key);
        }
        
        // Update the tracked keys
        $remainingKeys = array_diff($keys, $providerKeys);
        Cache::put($this->prefix . '_keys', $remainingKeys, $this->ttl);
    }
}
