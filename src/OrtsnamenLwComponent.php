<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class OrtsnamenLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'Ortsnamen';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('ortsnamen');
    }

    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    protected function processResults($results)
    {
        if (!$results || (!is_array($results) && !is_object($results))) {
            return [];
        }

        // Convert to array if it's an object to handle both cases
        if (is_object($results)) {
            $results = (array) $results;
        }

        $processedResults = [];
        
        foreach ($results as $item) {
            // Handle both array and object formats
            $getValue = function($item, $key, $default = '') {
                if (is_array($item)) {
                    return $item[$key] ?? $default;
                } elseif (is_object($item)) {
                    return $item->$key ?? $default;
                }
                return $default;
            };

            $coordinates = $getValue($item, 'coordinates');
            $id = $getValue($item, 'id');
            $permalink = $getValue($item, 'permalink');
            $types = $getValue($item, 'types', []);
            
            // Use permalink if available, otherwise construct URL
            $url = $permalink ?: ($id ? "https://www.ortsnamen.ch/de/{$id}" : '');
            
            $processedResults[] = [
                'provider_id' => $id,
                'preferredName' => $getValue($item, 'name'),
                'municipality' => $getValue($item, 'municipality'),
                'canton' => $getValue($item, 'canton'),
                'types' => is_array($types) ? $types : [$types],
                'lat' => is_object($coordinates) ? ($coordinates->lat ?? '') : ($coordinates['lat'] ?? ''),
                'lng' => is_object($coordinates) ? ($coordinates->lng ?? '') : ($coordinates['lng'] ?? ''),
                'url' => $url,
                'permalink' => $permalink,
                'provider' => 'ortsnamen',
            ];
        }

        return $processedResults;
    }

    public function mount($model, string $search = '', array $params = [])
    {
        // Set default search if empty
        if (empty(trim($search))) {
            $search = 'Zürich';
        }

        parent::mount($model, $search, $params);
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $full_json = preg_replace('/[\x00-\x1F]/','', $full_json);

        $data = [
            'provider' => 'ortsnamen',
            'provider_id' => $provider_id,
            'url' => $url,
            'full_json' => $this->processFullJson($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);
        $this->dispatch('resourcesChanged');
        event(new \KraenzleRitter\ResourcesComponents\Events\ResourceSaved($resource, $this->model->id));
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
