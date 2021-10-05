<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Ortsnamen
{
    public $body;

    public $url;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://search.ortsnamen.ch/de/api/']);
    }

    public function search(string $search, $params)
    {
        if (!$search) {
            return [];
        }

        $search = str_replace(',', ' ', $search);
        try {
            $response = $this->client->get('search?q=' . $search);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return [];
        }

        $body = json_decode($response->getBody());

        return $body->results;

    }

}
