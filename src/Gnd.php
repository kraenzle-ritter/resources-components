<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

/**
 * GND queries
 * cf . https://de.wikipedia.org/wiki/Hilfe:GND
 * cf. https://lobid.org/gnd/api
 *
 * Gnd::search('string', $params) : object
 * params:
 *      - field => 'preferredName',
 *      - filter => ['type' => 'Person'] ✔
 *      - from => 2
 *      - size (integer, default 20) ✔
 *      - format (default and only: json) ✔
 *      - formatFields
 */
class Gnd extends AbstractProvider
{
    public $filter_types = [
        'Person',
        'CorporateBody',
        'ConferenceOrEvent',
        'PlaceOrGeographicName',
        'Work',
        'PlaceOrGeographicName',
        'SubjectHeading',
        'Family'
    ];

    public function getBaseUrl(): string
    {
        return 'https://lobid.org/gnd/';
    }

    public function getProviderName(): string
    {
        return 'GND';
    }

    public function search(string $search, $params = [])
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        $searchQuery = 'search?q=' . urlencode($search);
        $filters = $params['filters'] ?? [];
        $size = $params['limit'] ?? $this->getConfigValue('limit', 5);

        $searchQuery .= $this->buildFilter($filters) . '&size=' . $size . '&format=json';

        $result = $this->makeRequest('GET', $searchQuery);

        if ($result && isset($result->totalItems) && $result->totalItems > 0) {
            return $result;
        }

        return null;
    }

    public function buildFilter(array $filters = []) : string
    {
        if (!$filters) {
            return '';
        }

        $filter = str_replace('=', ':', http_build_query($filters, null, ' AND '));

        return '&filter=' . $filter;
    }
}
