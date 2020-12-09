<?php

namespace KraenzleRitter\ResourcesComponents;

use Wikidata\Wikidata as Wiki;

/**
 * Wikidata queries
 */
class Wikidata
{
    /**
     * [search description]
     * @param  string $search [description]
     * @param  array  $params keys: locale, limit
     * @return [type]         [description]
     */
    public function search(string $search, $params = [])
    {
        $client = new Wiki();

        $lang = $params['locale'] ?? config('sources-components.wikidata.locale') ?? 'de';
        $limit = $params['limit'] ?? config('sources-components.wikidata.limit') ?? 5;

        $results = $client->search($search, $lang, $limit);

        return count($results) ? $results : [];
    }
}
