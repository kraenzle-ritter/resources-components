<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

/**
 * Metagrid queries
 *
 * Use the Metagrid API to search for concordances
 * (new Metagrid())->search('Karl Barth');
 */
class Metagrid extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return 'https://api.metagrid.ch/';
    }

    public function getProviderName(): string
    {
        return 'Metagrid';
    }

    public function search(string $search, array $params = [])
    {
        if (!$search) {
            return [];
        }

        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        $search = str_replace(',', ' ', $search);
        $limit = $params['limit'] ?? 10;

        $queryString = "search?query=" . urlencode($search) . "&group=1&_format=json&take=" . $limit;

        $result = $this->makeRequest('GET', $queryString);

        if (!$result || !isset($result->meta->total) || $result->meta->total == 0) {
            return [];
        }

        return $result->concordances ?? [];
    }

    /**
     * Get concordance details by ID
     * https://api.metagrid.ch/concordance/47451.json
     *
     * @param string $id
     * @return object|null
     */
    public function getConcordance(string $id): ?object
    {
        $result = $this->makeRequest('GET', "concordance/{$id}.json");
        return $result;
    }

    /**
     * Compose a name from resource metadata
     *
     * @param object $resource
     * @return string
     */
    public function composeName(object $resource): string
    {
        if (!isset($resource->metadata)) {
            return '';
        }

        if (isset($resource->metadata->first_name) && isset($resource->metadata->last_name)) {
            return $resource->metadata->last_name . ', ' . $resource->metadata->first_name;
        }

        return $resource->metadata->name ?? $resource->metadata->last_name ?? '';
    }

    /**
     * Compose date information from resource metadata
     *
     * @param object $resource
     * @param bool $full
     * @return string
     */
    public function composeDates(object $resource, bool $full = true): string
    {
        $date = '';

        if (isset($resource->metadata->birth_date)) {
            $date = ' *' . substr($resource->metadata->birth_date, 0, 4);
        }

        if (isset($resource->metadata->death_date)) {
            $date .= ' †' . substr($resource->metadata->death_date, 0, 4);
        }

        return $date ? ' (' . trim($date) . ')' : '';
    }
}
