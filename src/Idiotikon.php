<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Idiotikon
{
    public $body;

    public $url;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://digital.idiotikon.ch/api/']);
    }

    public function search(string $search, $params)
    {
        if (!$search) {
            return [];
        }

        $search = str_replace(',', ' ', $search);
        try {
            $response = $this->client->get('lemmata?query=' . $search);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return [];
        }

        $body = json_decode($response->getBody());

        return $body->results;

    }

}
