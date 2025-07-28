<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class GeonamesLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'Geonames';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('geonames');
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
            $search = 'Cassirer';
        }

        parent::mount($model, $search, $params);
    }
}
