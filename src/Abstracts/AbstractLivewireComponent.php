<?php

namespace KraenzleRitter\ResourcesComponents\Abstracts;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

abstract class AbstractLivewireComponent extends Component
{
    public $search = '';
    public $queryOptions = [];
    public $model;
    public $resourceable_id;
    public $provider;
    public $saveMethod = 'updateOrCreateResource';
    public $removeMethod = 'removeResource';
    public $errorMessage = '';
    public $hasError = false;

    protected $listeners = ['resourcesChanged' => 'render'];

    /**
     * Mount the component
     *
     * @param mixed $model
     * @param string $search
     * @param array $params
     */
    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;
        $this->search = trim($search);
        $this->queryOptions = $params['queryOptions'] ?? $this->getDefaultOptions();
        $this->provider = $this->getProviderName();
        $this->clearError();
    }

    /**
     * Clear error state
     */
    public function clearError()
    {
        $this->hasError = false;
        $this->errorMessage = '';
    }

    /**
     * Set error state with message
     *
     * @param string $message
     * @param \Exception $exception
     */
    protected function setError(string $message, \Exception $exception = null)
    {
        $this->hasError = true;
        $this->errorMessage = $message;
        
        // Log the actual exception for debugging
        if ($exception) {
            \Log::error("Provider {$this->getProviderName()} error: " . $exception->getMessage(), [
                'search' => $this->search,
                'params' => $this->queryOptions,
                'exception' => $exception
            ]);
        }
    }

    /**
     * Perform search with error handling
     *
     * @return array
     */
    protected function performSearch(): array
    {
        if (!$this->search) {
            return [];
        }

        try {
            $this->clearError();
            $client = $this->getProviderClient();
            $resources = $client->search($this->search, $this->queryOptions);
            return $this->processResults($resources);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->setError(
                "Network error while searching '{$this->search}'. Please check your internet connection and try again.",
                $e
            );
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->setError(
                "Cannot connect to {$this->getProviderName()} service. The service might be temporarily unavailable.",
                $e
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 400) {
                $this->setError(
                    "Invalid search query '{$this->search}'. Please try a different search term.",
                    $e
                );
            } else {
                $this->setError(
                    "Error from {$this->getProviderName()} service. Please try again later.",
                    $e
                );
            }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->setError(
                "{$this->getProviderName()} service is temporarily unavailable. Please try again later.",
                $e
            );
        } catch (\InvalidArgumentException $e) {
            $this->setError(
                "Invalid search parameters for '{$this->search}'. Please check your input.",
                $e
            );
        } catch (\Exception $e) {
            $this->setError(
                "An unexpected error occurred while searching '{$this->search}'. Please try again.",
                $e
            );
        }

        return [];
    }

    /**
     * Save a resource
     *
     * @param string $provider_id
     * @param string $url
     * @param string|null $full_json
     */
    public function saveResource($provider_id, $url, $full_json = null)
    {
        try {
            $this->clearError();
            
            $data = [
                'provider' => $this->provider,
                'provider_id' => $provider_id,
                'url' => $url,
                'full_json' => $this->processFullJson($full_json)
            ];

            $resource = $this->model->{$this->saveMethod}($data);

            if (method_exists($this->model, 'saveMoreResources')) {
                $this->model->saveMoreResources(strtolower($this->provider));
            }

            $this->dispatch('resourcesChanged');
            event(new ResourceSaved($resource, $this->model->id));
        } catch (\Exception $e) {
            $this->setError(
                "Failed to save resource. Please try again.",
                $e
            );
        }
    }

    /**
     * Remove a resource
     *
     * @param string $url
     */
    public function removeResource($url)
    {
        try {
            $this->clearError();
            
            Resource::where([
                'url' => $url
            ])->delete();
            
            $this->dispatch('resourcesChanged');
        } catch (\Exception $e) {
            $this->setError(
                "Failed to remove resource. Please try again.",
                $e
            );
        }
    }

    /**
     * Set error message and log the exception
     *
     * @param string $message
     * @param \Exception|null $exception
     */
    protected function setError(string $message, ?\Exception $exception = null): void
    {
        $this->hasError = true;
        $this->errorMessage = $message;
        
        if ($exception) {
            \Log::error("ResourcesComponents Error in {$this->getProviderName()}: " . $exception->getMessage(), [
                'search' => $this->search,
                'provider' => $this->getProviderName(),
                'exception' => $exception,
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }

    /**
     * Clear error state
     */
    protected function clearError(): void
    {
        $this->hasError = false;
        $this->errorMessage = '';
    }

    /**
     * Process the full JSON data
     *
     * @param string|null $full_json
     * @return mixed
     */
    protected function processFullJson($full_json)
    {
        if ($full_json === null) {
            return null;
        }

        if (is_string($full_json)) {
            $decoded = json_decode($full_json);
            return $decoded ?: $full_json;
        }

        return $full_json;
    }

    /**
     * Get the view name for this component
     *
     * @return string
     */
    protected function getViewName(): string
    {
        $providerName = strtolower($this->getProviderName());
        $componentName = $providerName . '-lw-component';

        $customView = "vendor.kraenzle-ritter.livewire.{$componentName}";
        $defaultView = "resources-components::{$componentName}";

        return view()->exists($customView) ? $customView : $defaultView;
    }

    /**
     * Get default options for this provider
     *
     * @return array
     */
    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    /**
     * Get the provider name
     *
     * @return string
     */
    abstract protected function getProviderName(): string;

    /**
     * Get the provider client instance
     *
     * @return mixed
     */
    abstract protected function getProviderClient();

    /**
     * Process the search results before passing to view
     *
     * @param mixed $results
     * @return mixed
     */
    abstract protected function processResults($results);
}
