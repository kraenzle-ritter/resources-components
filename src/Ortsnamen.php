<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

class Ortsnamen extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return 'https://search.ortsnamen.ch/de/api/';
    }

    public function getProviderName(): string
    {
        return 'Ortsnamen';
    }

    public function search(string $search, array $params = [])
    {
        if (!$search) {
            return [];
        }

        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        $search = str_replace(',', ' ', $search);

        $queryString = 'search?q=' . urlencode($search);

        $result = $this->makeRequest('GET', $queryString);

        return $result->results ?? [];
    }
}
