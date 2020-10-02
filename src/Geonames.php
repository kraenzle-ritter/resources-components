<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\GuzzleException;

class Geonames
{
    public $client;

    public $username;

    public $body;

    public $query_params = [
        'maxRows' => 5,            // Default is 100, the maximal allowed value is 1000.
        'style' => 'FULL',          // Verbosity of returned xml document, default = MEDIUM
        //'continentCode' => 'EU',    // Restricts the search for toponym of the given continent
        //'countryBias' => 'CH',    // Records from the countryBias are listed first
        'type' => 'JSON',           // the format type of the returned document, default = xml
        'isNameRequired' => 'true'  // At least one of the search term needs to be part of the place name
    ];

    public function __construct()
    {
        // https://www.geonames.org/export/geonames-search.html
        $this->username = config('resources-components.geonames.username');

        /* https://datahub.io/core/continent-codes
        if (isset(app('settings')['geonames_continentCode']) && in_array(app('settings')['geonames_continentCode'], ['AF', 'AS', 'EU', 'NA', 'OC', 'SA', 'AN'])) {
            $this->query_params['continentCode'] =  app('settings')['geonames_continentCode'];
        } else {
            unset($this->query_params['continentCode']);
        }*/

        $this->client = new Client(['base_uri' => 'http://api.geonames.org/']);
    }

    public function search($string, $params = [])
    {
        $this->query_params =  $params ?: $this->query_params;

        $this->query_params = array_merge(['q' => $string, 'username' => $this->username], $this->query_params);
        $query_string = static::paramsToQueryString($this->query_params);

        $search = 'searchJSON?' . $query_string;

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

    public function toAntonArray(): array
    {
        $array = [];
        $i = 0;
        $locale_default = app('settings')['locales']['value'][0];
        foreach ($this->body->geonames as $row) {
            $array[$i]['place']['name'] = Str::slug($row->toponymName);
            if (isset($row->alternateNames)) {
                $array[$i]['place']['label'] = $this->toLabelArray($row->alternateNames);
            }
            if (empty($array[$i]['place']['label'])) {
                $array[$i]['place']['label'][env('LOCALES_DEFAULT')] = $row->toponymName;
            }

            $array[$i]['place']['country_code'] = $row->countryCode ?? '';
            $array[$i]['place']['country_name'] = $row->countryName ?? '';
            $array[$i]['place']['fcl'] = $row->fcl; //abstract feature code

            $array[$i]['place']['location'] = $row->lng . ', ' . $row->lat;

            $array[$i]['resource']['provider'] = 'geonames';
            $array[$i]['resource']['resource_id'] = $row->geonameId;
            $array[$i]['resource']['full_json'] = json_encode($row);

            $array[$i]['resource']['url'] = 'http://api.geonames.org/get?geonameId=' . $row->geonameId . '&style=json&username=antonatgeonames';

            $xml = $this->getPlaceByGeonameId($row->geonameId);
            $array[$i]['resource']['full_json'] = json_encode($xml, JSON_UNESCAPED_UNICODE);
            $i++;
        }

        return $array;
    }

    private function toLabelArray(array $alternateNames): array
    {
        $labelArray = [];
        foreach ($alternateNames as $label) {
            if (in_array($label->lang, config('custom.locales'))) {
                $labelArray[$label->lang] = $label->name;
            }
        }
        return $labelArray;
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
