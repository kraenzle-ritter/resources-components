<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Anton
{
    public $body;
    public $url;
    private $token;
    private $providerKey;

    public function __construct(string $providerKey)
    {
        $this->url = config("resources-components.providers.{$providerKey}.base_url"); //

        $this->token = config("resources-components.providers.{$providerKey}.api_token");
    }

    public function search(
        string $search,
        array $params = [],
        string $endpoint = 'objects')
    {
        $this->client = new Client(['base_uri' => $this->url . $endpoint]);

        $this->query_params = $params ?: $this->query_params;

        $limit = $params['size'] ??
            config("resources-components.providers.{$this->providerKey}.limit") ??
            config('sources-components.limit') ??
            5;

        $this->query_params['perPage'] = $limit;
        $this->query_params['page'] = $params['page'] ?? 1;
        unset($params['size']);

        $this->query_params = array_merge(['?search' => $search, 'api_token' => $this->token], $this->query_params);
        $query_string = Params::toQueryString($this->query_params);
        $search = $query_string;
        try {
            $response = $this->client->get($search);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody());
            }
        } catch (RequestException $e) {
            \Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                \Log::debug(Psr7\str($e->getResponse()));
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $result = [];
        }

        return $result->data ?? [];
    }
}
