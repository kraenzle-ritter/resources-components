<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class AntonLwComponent extends AbstractLivewireComponent
{
    public $endpoint = 'objects';

    protected function getProviderName(): string
    {
        return 'Anton';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('anton');
    }

    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    protected function processResults($results)
    {
        return $results;
    }

    public function mount($model, string $search = '', array $params = [], string $endpoint = 'objects')
    {
        $this->endpoint = $endpoint;

        // Set default search if empty
        if (empty(trim($search))) {
            $search = 'Cassirer';
        }

        parent::mount($model, $search, $params);
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => config('resources-components.anton.provider-slug'),
            'provider_id' => $provider_id,
            'url' => config('resources-components.anton.url'). '/' . $this->endpoint . '/' . $provider_id,
            'full_json' => $this->processFullJson($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);
        $this->dispatch('resourcesChanged');
        event(new \KraenzleRitter\ResourcesComponents\Events\ResourceSaved($resource, $this->model->id));
    }

    public function render()
    {
        $results = [];

        if ($this->search) {
            $client = $this->getProviderClient();
            $resources = $client->search($this->search, $this->queryOptions, $this->endpoint);
            $results = $this->processResults($resources);
        }

        return view($this->getViewName(), [
            'results' => $results
        ]);
    }
}
