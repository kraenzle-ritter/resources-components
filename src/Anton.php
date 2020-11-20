<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Anton
{
    public $body;

    public $url;

    private $token;

    public function __construct()
    {
        $this->url = config('resources-components.anton.api_url');
        $this->token = config('resources-components.anton.token');
    }

    public function search($string, $params = [], $endpoint = 'objects')
    {
        $this->client = new Client(['base_uri' => $this->url .'/' . $endpoint]);

        $this->query_params =  $params ?: $this->query_params;
        $this->query_params['perPage'] = $params['size'] ?? config('sources-components.anton.limit') ?? 5;
        $this->query_params['page'] = $params['page'] ?? 1;
        unset($params['size']);

        $this->query_params = array_merge(['?search' => $string, 'api_token' => $this->token], $this->query_params);
        $query_string = Params::toQueryString($this->query_params);
        $search = $query_string;

        try {
            $response = $this->client->get($search);
        } catch (RequestException $e) {
                \Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                \Log::debug(Psr7\str($e->getResponse()));
            }
        }

        if ($response->getStatusCode() === 200) {
            $result = json_decode($response->getBody());
        }

        return $result->data ?? [];
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
