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

    public function search(string $search, $params = [])
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
        
        // Limit anwenden, falls angegeben
        $limit = $params['limit'] ?? config('resources-components.limit') ?? 5;
        if ($limit && is_array($body->results)) {
            return array_slice($body->results, 0, $limit);
        }

        return $body->results;

    }

}
