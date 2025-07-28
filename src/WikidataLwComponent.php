<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class WikidataLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'Wikidata';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('wikidata');
    }

    protected function getDefaultOptions(): array
    {
        return ['locale' => 'de', 'limit' => 5];
    }

    protected function processResults($results)
    {
        if (!$results || !is_array($results)) {
            return [];
        }

        $processedResults = [];

        foreach ($results as $result) {
            $processedResults[] = [
                'title' => $result->title ?? $result->label ?? '',
                'description' => $result->description ?? '',
                'url' => $result->concepturi ?? "https://www.wikidata.org/wiki/{$result->id}",
                'provider_id' => $result->id ?? '',
                'id' => $result->id ?? '',
                'concepturi' => $result->concepturi ?? '',
                'pageid' => $result->pageid ?? '',
                'repository' => $result->repository ?? '',
                'label' => $result->label ?? '',
                'match' => $result->match ?? []
            ];
        }

        return $processedResults;
    }

    public function render()
    {
        $results = [];

        if ($this->search) {
            $client = $this->getProviderClient();
            $resources = $client->search($this->search, $this->queryOptions);
            $results = $this->processResults($resources);
        }

        return view($this->getViewName(), [
            'results' => $results
        ]);
    }
}
