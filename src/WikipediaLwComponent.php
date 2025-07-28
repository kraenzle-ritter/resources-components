<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class WikipediaLwComponent extends AbstractLivewireComponent
{
    public $base_url;

    protected function getProviderName(): string
    {
        return 'Wikipedia';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('wikipedia');
    }

    protected function getDefaultOptions(): array
    {
        return ['locale' => 'de', 'limit' => 5];
    }

    public function mount($model, string $search = '', array $params = [])
    {
        parent::mount($model, $search, $params);

        $locale = $params['locale'] ?? 'de';
        $this->base_url = "https://{$locale}.wikipedia.org/wiki/";
    }

    protected function processResults($results)
    {
        if (!$results || !is_array($results)) {
            return [];
        }

        $processedResults = [];

        foreach ($results as $result) {
            $processedResults[] = [
                'title' => $result->title ?? '',
                'snippet' => strip_tags($result->snippet ?? ''),
                'url' => $this->base_url . str_replace(' ', '_', $result->title ?? ''),
                'provider_id' => $result->title ?? '',
                'pageid' => $result->pageid ?? '',
                'size' => $result->size ?? 0,
                'wordcount' => $result->wordcount ?? 0,
                'timestamp' => $result->timestamp ?? ''
            ];
        }

        return $processedResults;
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => str_replace(' ', '_', $url),
            'full_json' => $this->processFullJson($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);

        if (method_exists($this->model, 'saveMoreResources')) {
            $this->model->saveMoreResources('wikipedia');
        }

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
