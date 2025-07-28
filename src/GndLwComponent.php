<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class GndLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'GND';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('gnd');
    }

    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    protected function processResults($resources)
    {
        if (!isset($resources) || !isset($resources->member) || !count($resources->member)) {
            return [];
        }

        $results = [];

        foreach ($resources->member as $resource) {
            $result = [
                'preferredName' => $resource->preferredName ?? '',
                'gndIdentifier' => $resource->gndIdentifier ?? '',
                'type' => is_array($resource->type) ? $resource->type : [$resource->type],
                'url' => "https://d-nb.info/gnd/{$resource->gndIdentifier}",
                'provider_id' => $resource->gndIdentifier ?? '',
                'dateOfBirth' => $resource->dateOfBirth ?? [],
                'dateOfDeath' => $resource->dateOfDeath ?? [],
                'biographicalOrHistoricalInformation' => $resource->biographicalOrHistoricalInformation ?? [],
                'sameAs' => []
            ];

            // Process dates for persons
            if (in_array('Person', $result['type'])) {
                $date_start = isset($resource->dateOfBirth) ? $resource->dateOfBirth[0] : '';
                $date_end = isset($resource->dateOfDeath) ? $resource->dateOfDeath[0] : '';

                if ($date_start || $date_end) {
                    $result['dateLine'] = $date_start . ' – ' . $date_end;
                }
            }

            // Process other resources (sameAs)
            if (isset($resource->sameAs)) {
                foreach ($resource->sameAs as $sameAs) {
                    $name = $sameAs->collection->abbr ?? $sameAs->collection->name ?? '';
                    $name = strpos($sameAs->id, 'isni.org') > 0 ? 'ISNI' : $name;

                    if ($name !== 'DNB') {
                        $result['sameAs'][$name] = $sameAs->id;
                    }
                }
            }

            $results[] = $result;
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
