<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;

/**
 * GND queries
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
class Gnd
{
    public $client;

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

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://lobid.org/gnd/']);
    }

    public function search(string $search, $params = [])
    {
        $search = 'search?q=' . urlencode($search);

        $filters = $params['filters'] ?? [];

        $size = $params['limit'] ?? config('sources-components.gnd.limit') ?? 20;

        $search = $search . $this->buildFilter($filters) . '&size=' . $size  .'&format=json';

        try {
            $response = $this->client->get($search);
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents());
            if ($result->totalItems > 0) {
                return $result;
            }
        }
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
