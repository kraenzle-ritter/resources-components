<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;
use Illuminate\Support\Facades\Log;

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
        if (!$results || !is_object($results) || !property_exists($results, 'geonames')) {
            return [];
        }

        $processedResults = [];
        
        foreach ($results->geonames as $geoname) {
            $description = [];
            if (!empty($geoname->countryName ?? '')) {
                $description[] = $geoname->countryName;
            }
            if (!empty($geoname->adminName1 ?? '')) {
                $description[] = $geoname->adminName1;
            }
            
            $processedResults[] = [
                'provider_id' => $geoname->geonameId ?? '',
                'preferredName' => $geoname->toponymName ?? $geoname->name ?? '',
                'description' => implode(', ', $description),
                'countryName' => $geoname->countryName ?? '',
                'adminName1' => $geoname->adminName1 ?? '',
                'lat' => $geoname->lat ?? '',
                'lng' => $geoname->lng ?? '',
                'url' => isset($geoname->geonameId) ? "https://www.geonames.org/{$geoname->geonameId}" : '',
                'provider' => 'geonames',
            ];
        }

        return $processedResults;
    }

    public function mount($model, string $search = '', array $params = [])
    {
        // Set default search if empty
        if (empty(trim($search))) {
            $search = 'Cassirer';
        }

        parent::mount($model, $search, $params);
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
