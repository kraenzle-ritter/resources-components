<?php

namespace KraenzleRitter\ResourcesComponents;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Anton
{
    public $body;
    public $url;
    private $token;
    private $providerKey;
    private $client;
    private $query_params = [];

    public function __construct(string $providerKey)
    {
        $this->providerKey = $providerKey;
        $this->url = config("resources-components.providers.{$providerKey}.base_url"); //

        $this->token = config("resources-components.providers.{$providerKey}.api_token");
    }

    public function search(
        string $search,
        array $params = [],
        string $endpoint = 'actors')
    {
        // Make sure URL ends with a slash
        $baseUrl = rtrim($this->url, '/') . '/';

        // Fix the base_uri construction to include trailing slash for endpoint
        $this->client = new Client(['base_uri' => $baseUrl]);

        $this->query_params = $params ?: $this->query_params;

        $limit = $params['limit'] ?? 
            $params['size'] ?? // Support both 'size' and 'limit' parameters
            config("resources-components.providers.{$this->providerKey}.limit") ??
            config('resources-components.limit') ??
            5;

        $this->query_params['perPage'] = $limit;
        $this->query_params['page'] = $params['page'] ?? 1;

        // Build proper query params - no ? in the key name
        $this->query_params = array_merge(['search' => $search], $this->query_params);

        // Add API token only if it exists
        if ($this->token) {
            $this->query_params['api_token'] = $this->token;
        }

        // Convert to query string
        $query_string = Params::toQueryString($this->query_params);

        // Construct the full URL
        $fullUrl = $endpoint . '?' . ltrim($query_string, '?');
        try {
            \Log::debug("Anton provider: Making request to " . $baseUrl . $fullUrl);
            $response = $this->client->get($fullUrl);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody());
                \Log::debug("Anton provider: Got successful response");
            }
        } catch (RequestException $e) {
            \Log::error('Anton provider - Request Exception: ' . $e->getMessage());
            if ($e->hasResponse()) {
                \Log::error('Response: ' . $e->getResponse()->getBody()->getContents());
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            \Log::error('Anton provider - Client Exception: ' . $e->getMessage());
            if ($e->hasResponse()) {
                \Log::error('Response: ' . $e->getResponse()->getBody()->getContents());
            }
            $result = [];
        } catch (\Exception $e) {
            \Log::error('Anton provider - General Exception: ' . $e->getMessage());
            $result = [];
        }

        // Make sure we have a valid result
        if (isset($result) && isset($result->data) && is_array($result->data)) {
            return $result->data;
        }

        // Return empty array if no results or result is not as expected
        return [];
    }
}
