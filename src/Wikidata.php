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

        $lang = $params['locale'] ?? 'de';
        $limit = $params['limit'] ?? 5;

        $results = $client->search($search, $lang, $limit);

        return $results;
    }


}
