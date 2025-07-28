<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;
use Illuminate\Support\Facades\Log;

class MetagridLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'Metagrid';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('metagrid');
    }

    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    protected function processResults($resources)
    {
        if (!isset($resources) || !is_array($resources)) {
            return [];
        }

        $results = [];

        foreach ($resources as $resource) {
            try {
                // Ensure resource is an object for composeName/composeDates methods
                $resourceObject = is_object($resource) ? $resource : (object) $resource;
                
                // Convert to array for consistent access
                $resourceArray = (array) $resource;
                
                // Get composed name and dates
                $preferredName = $this->getProviderClient()->composeName($resourceObject);
                $dates = $this->getProviderClient()->composeDates($resourceObject);
                
                // Fallback name extraction if composeName doesn't work
                if (empty($preferredName)) {
                    $metadata = $resourceArray['metadata'] ?? null;
                    if (is_array($metadata)) {
                        $preferredName = $metadata['name'] ?? 
                                       ($metadata['last_name'] ?? '') . 
                                       ($metadata['first_name'] ? ', ' . $metadata['first_name'] : '');
                    } elseif (is_object($metadata)) {
                        $preferredName = $metadata->name ?? 
                                       ($metadata->last_name ?? '') . 
                                       ($metadata->first_name ? ', ' . $metadata->first_name : '');
                    }
                }
                
                $result = [
                    'preferredName' => $preferredName ?: ($resourceArray['name'] ?? 'Unbekannt'),
                    'dates' => $dates,
                    'url' => $resourceArray['uri'] ?? $resourceArray['url'] ?? '',
                    'provider_id' => $resourceArray['id'] ?? '',
                    'type' => $resourceArray['type'] ?? [],
                    'description' => $resourceArray['description'] ?? '',
                    'metadata' => $resourceArray['metadata'] ?? null,
                    'provider' => 'metagrid',
                ];

                $results[] = $result;
            } catch (\Exception $e) {
                // Log error but continue processing other results
                Log::warning("Error processing Metagrid result: " . $e->getMessage(), [
                    'resource' => $resource,
                    'exception' => $e
                ]);
                continue;
            }
        }

        return $results;
    }

    public function render()
    {
        $results = $this->performSearch();

        return view($this->getViewName(), [
            'results' => $results,
            'hasError' => $this->hasError,
            'errorMessage' => $this->errorMessage
        ]);
    }
}