<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class IdiotikonLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'Idiotikon';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('idiotikon');
    }

    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    protected function processResults($results)
    {
        if (!$results || !is_array($results)) {
            return [];
        }

        $processedResults = [];
        
        foreach ($results as $item) {
            $processedResults[] = [
                'provider_id' => $item->id ?? '',
                'preferredName' => $item->lemma ?? '',
                'definition' => $item->definition ?? '',
                'volume' => $item->volume ?? '',
                'column' => $item->column ?? '',
                'url' => isset($item->id) ? "https://www.idiotikon.ch/wortgeschichten/{$item->id}" : '',
                'provider' => 'idiotikon',
            ];
        }

        return $processedResults;
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $full_json = preg_replace('/[\x00-\x1F]/','', $full_json);

        $data = [
            'provider' => 'idiotikon',
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
