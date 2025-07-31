<?php

namespace KraenzleRitter\ResourcesComponents\Traits;

use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Helpers\TextHelper;

/**
 * Trait for provider Livewire components
 */
trait ProviderComponentTrait
{
    /**
     * Extract the first sentence from a text
     *
     * @param string $text The text to extract from
     * @param int $maxLength Maximum length of the sentence (0 = unlimited)
     * @return string The first sentence
     */
    public function extractFirstSentence($text, $maxLength = 150)
    {
        return TextHelper::extractFirstSentence($text, $maxLength);
    }

    /**
     * Format a URL for display and linking
     *
     * @param string $url The URL to format
     * @return string Formatted URL display
     */
    public function formatUrl($url)
    {
        return $url;
    }

    /**
     * Prepare search results for display
     *
     * @param array|object $results The raw results from the provider
     * @return array Prepared results
     */
    public function prepareResultsForDisplay($results)
    {
        // Can be overridden in derived components
        return $results;
    }
    
    /**
     * Show all search results by updating the query options
     * 
     * @return void
     */
    public function showAllResults()
    {
        // Increase the limit for displaying all results
        $this->queryOptions['limit'] = 50;
        $this->showAll = true;
        
        if (class_exists('\Log')) {
            \Log::debug('ShowAllResults called: setting limit to 50 and showAll=true');
        }
        
        // If a search is active, we execute it again
        if (!empty($this->search)) {
            $this->updatedSearch($this->search);
        }
    }
    
    /**
     * Debug-Methode für die Komponente (nur im Debug-Modus)
     */
    public function debugComponent()
    {
        if (!class_exists('\Log')) {
            return;
        }
        
        \Log::debug('Component Debug', [
            'class' => get_class($this),
            'search' => $this->search ?? null,
            'showAll' => $this->showAll ?? false,
            'queryOptions' => $this->queryOptions ?? [],
            'model' => $this->model ? get_class($this->model) : null,
        ]);
    }

    /**
     * Save a resource to the associated model
     *
     * @param mixed $model The model to associate the resource with
     * @param array $resourceData Resource data array
     * @return Resource The saved resource
     */
    protected function saveResourceToModel($model, array $resourceData)
    {
        // Check if model has required methods
        if (!method_exists($model, 'updateOrCreateResource')) {
            throw new \Exception('Model does not implement updateOrCreateResource method');
        }

        // Required fields
        $requiredFields = ['provider', 'url'];
        foreach ($requiredFields as $field) {
            if (!isset($resourceData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Save the resource
        $resource = $model->updateOrCreateResource(
            $resourceData['provider'],
            $resourceData['url'],
            $resourceData['title'] ?? null,
            $resourceData['data'] ?? null
        );

        // Notify other components that resources have changed
        $this->dispatch('resourcesChanged');

        return $resource;
    }

    /**
     * Generic error handler for search operations
     *
     * @param \Exception $e The exception to handle
     * @return void
     */
    protected function handleSearchError(\Exception $e)
    {
        $this->error = 'Error: ' . $e->getMessage();
        $this->results = [];

        if (class_exists('\Log')) {
            \Log::error('Search error in ' . get_class($this) . ': ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    /**
     * Get configuration for the current provider
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    protected function getProviderConfig(string $key, $default = null)
    {
        if (!isset($this->providerKey)) {
            return $default;
        }

        return config("resources-components.providers.{$this->providerKey}.{$key}", $default);
    }
}
