<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;
use Illuminate\Support\Facades\Http;
use KraenzleRitter\ResourcesComponents\Helpers\Params;

class Geonames extends AbstractProvider
{
    protected $username;
    protected $query_params = [
        'style' => 'FULL',          // Verbosity of returned xml document, default = MEDIUM
        'type' => 'JSON',           // the format type of the returned document, default = xml
        'isNameRequired' => 'true'  // At least one of the search term needs to be part of the place name
    ];

    public function getBaseUrl(): string
    {
        return 'http://api.geonames.org/';
    }

    public function getProviderName(): string
    {
        return 'Geonames';
    }

    protected function setDefaultParams(): void
    {
        parent::setDefaultParams();

        // https://www.geonames.org/export/geonames-search.html
        $this->username = $this->getConfigValue('username');

        $this->query_params['maxRows'] = $this->getConfigValue('limit', 5);
        $this->query_params['continentCode'] = $this->getConfigValue('continent-code', '');
        $this->query_params['countryBias'] = $this->getConfigValue('country-bias', '');
        $this->query_params = array_filter($this->query_params);
    }

    public function search(string $search, array $params = [])
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        $queryParams = array_merge([
            'q' => $search,
            'username' => $this->username
        ], $this->query_params, $params);

        $queryString = 'searchJSON?' . Params::toQueryString($queryParams);

        $response = Http::get($this->getBaseUrl() . $queryString);

        if ($response->serverError()) {
            \Log::error(__METHOD__, ['guzzle server error (geonames)']);
            return [];
        }

        if ($response->successful()) {
            $result = $response->json();
            return $result['geonames'] ?? [];
        }

        return [];
    }

    /**
     * Get place details by Geoname ID
     *
     * @param string $id
     * @return \SimpleXMLElement|null
     */
    public function getPlaceByGeonameId(string $id): ?\SimpleXMLElement
    {
        try {
            $response = $this->makeRequest('GET', "get?geonameId={$id}&username={$this->username}");

            if ($response) {
                return simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching Geoname {$id}: " . $e->getMessage());
        }

        return null;
    }
}
