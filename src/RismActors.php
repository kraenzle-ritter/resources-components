<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class RismActors
{
    public $body;

    public $url;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'Accept' => 'application/ld-json',
                'X-API-Accept-Language' => 'de',
            ],

            'base_uri' => 'https://rism.online/search'
        ]);
    }

    public function search(string $search, $params)
    {
        if (!$search) {
            return [];
        }

        $search = str_replace(',', ' ', $search);
        try {
            $response = $this->client->get('search?q=' . $search.'&mode=people');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return [];
        }

        $body = json_decode($response->getBody());

        return $body->results;

    }

}
