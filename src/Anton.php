<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\GuzzleException;

class Anton
{
    public $client;

    public $username;

    public $body;

    public $query_params = [
        'perPage' => 5,  // Default is 100, the maximal allowed value is 1000
        'page' => 1
    ];

    public function __construct()
    {
        $this->url = config('resources-components.anton.api_url');
        $this->token = config('resources-components.anton.token');
    }

    public function search($string, $params = [], $endpoint = 'objects')
    {
        $this->client = new Client(['base_uri' => $this->url .'/' . $endpoint]);
        $this->query_params['perPage'] = $params['size'] ?? config('sources-components.anton.limit');
        unset($params['size']);
        //$this->query_params =  $params ?: $this->query_params;

        $this->query_params = array_merge(['?search' => $string, 'api_token' => $this->token], $this->query_params);
        $query_string = static::paramsToQueryString($this->query_params);
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



    /**
     * Convert Parameters Array to a Query String.
     *
     * Escapes values according to RFC 1738.
     *
     * @see http://forum.geonames.org/gforum/posts/list/8.page
     * @see rawurlencode()
     * @see https://github.com/Aternus/geonames-client/blob/master/src/Client.php
     *
     * @param array $params Associative array of query parameters.
     *
     * @return string The query string.
     */
    public static function paramsToQueryString(array $params = []) : string
    {
        $query_string = [];
        foreach ($params as $name => $value) {
            if (empty($name)) {
                continue;
            }
            if (is_array($value)) {
                // recursion case
                $result_string = static::paramsToQueryString($value);
                if (!empty($result_string)) {
                    $query_string[] = $result_string;
                }
            } else {
                // base case
                $value = (string)$value;
                $query_string[] = $name . '=' . rawurlencode($value);
            }
        }
        return implode('&', $query_string);
    }
}
