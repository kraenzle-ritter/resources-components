<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;
use Wikidata\Wikidata as WikidataBase;

/**
 * Wikidata queries
 */
class Wikidata extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return 'https://www.wikidata.org/';
    }

    public function getProviderName(): string
    {
        return 'Wikidata';
    }

    public function search(string $search, array $params = [])
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);
        
        $client = new WikidataBase();

        $lang = $params['locale'] ?? $this->getConfigValue('locale', 'de');
        $limit = $params['limit'] ?? $this->getConfigValue('limit', 5);

        $results = $client->search($search, $lang, $limit);

        if (count($results)) {
            return $results;
        }
        
        // Try with reversed search terms for names like "Lastname, Firstname"
        if (str_contains($search, ',')) {
            $array = explode(',', $search);
            $array = array_reverse($array);
            $reversedSearch = trim(join(' ', $array));
            $results = $client->search($reversedSearch, $lang, $limit);
        }

        return count($results) ? $results : [];
    }
}
