<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

/**
 * Wikidata queries
 */
class Wikidata extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return 'https://www.wikidata.org/w/api.php';
    }

    public function getProviderName(): string
    {
        return 'Wikidata';
    }

    public function search(string $search, array $params = [])
    {
        if (!$search) {
            return [];
        }

        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        $lang = $params['locale'] ?? $this->getConfigValue('locale', 'de');
        $limit = $params['limit'] ?? $this->getConfigValue('limit', 5);

        $queryParams = [
            'action' => 'wbsearchentities',
            'format' => 'json',
            'language' => $lang,
            'search' => $search,
            'limit' => $limit,
            'type' => 'item'
        ];

        $queryString = '?' . http_build_query($queryParams);
        $result = $this->makeRequest('GET', $queryString);

        $results = $result->search ?? [];

        // Try with reversed search terms for names like "Lastname, Firstname"
        if (empty($results) && str_contains($search, ',')) {
            $array = explode(',', $search);
            $array = array_reverse($array);
            $reversedSearch = trim(join(' ', $array));

            $queryParams['search'] = $reversedSearch;
            $queryString = '?' . http_build_query($queryParams);
            $reversedResult = $this->makeRequest('GET', $queryString);
            $results = $reversedResult->search ?? [];
        }

        // Convert arrays to objects to maintain consistency
        return $this->arrayToObject($results);
    }
}
