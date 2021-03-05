<?php

namespace KraenzleRitter\ResourcesComponents;

use Illuminate\Support\Facades\Http;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Geonames
{
    public $client;

    public $base_uri;

    public $username;

    public $body;

    public $query_params = [
        'style' => 'FULL',          // Verbosity of returned xml document, default = MEDIUM
        'type' => 'JSON',           // the format type of the returned document, default = xml
        'isNameRequired' => 'true'  // At least one of the search term needs to be part of the place name
    ];

    public function __construct()
    {
        // https://www.geonames.org/export/geonames-search.html
        $this->username = config('resources-components.geonames.username');

        $this->query_params['maxRows'] = config('resources-components.geonames.limit') ?? 5; // Default is 100, the maximal allowed value is 1000.
        $this->query_params['continentCode'] =  config('resources-components.geonames.continent-code') ?? '';
        $this->query_params['countryBias'] = config('resources-components.geonames.country-bias') ?? '';
        $this->query_params = array_filter($this->query_params);

        $this->base_uri = 'http://api.geonames.org/';
    }

    public function search($string, $params = [])
    {
        $this->query_params =  $params ?: $this->query_params;
        $this->query_params = array_merge(['q' => $string, 'username' => $this->username], $this->query_params);

        $query_string = Params::toQueryString($this->query_params);
        $search = 'searchJSON?' . $query_string;

        $response = HTTP::get($this->base_uri.$search);

        if ($response->serverError()) {
            \Log::error(__METHOD__, ['guzzle server error (geonames)']);
            return [];
        }

        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody());
        }

        return $result->geonames ?? [];
    }

    // http://api.geonames.org/get?geonameId=2658434&username=antonatgeonames
    public function getPlaceByGeonameId(string $id): \SimpleXMLElement
    {
        $response = $this->client->get('get?geonameId=' . $id . '&username=antonatgeonames');
        $xml = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);

        // this also works but has slightly other format therefore i dont change this (ak/2019-11-25)
        //$response2 = $this->client->get('getJSON?geonameId=' . $id . '&username=antonatgeonames');
        //$body =  $response2->getBody();

        return $xml;
    }
}
