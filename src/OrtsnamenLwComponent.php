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
        return $results;
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
