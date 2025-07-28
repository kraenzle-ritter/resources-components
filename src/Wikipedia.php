<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

/**
 * Wikipedia queries
 *
 * Use the Wikipedia API to search for articles
 */
class Wikipedia extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        $locale = $this->getConfigValue('locale', 'de');
        return "https://{$locale}.wikipedia.org/w/api.php";
    }

    public function getProviderName(): string
    {
        return 'Wikipedia';
    }

    protected function setDefaultParams(): void
    {
        parent::setDefaultParams();
        $this->defaultParams['locale'] = $this->getConfigValue('locale', 'de');
    }

    public function search(string $search, array $params = [])
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);

        $limit = $params['limit'] ?? 5;
        $locale = $params['locale'] ?? 'de';

        // Update base URI for the specific locale
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => "https://{$locale}.wikipedia.org/w/api.php"
        ]);

        $searchstring = trim(str_replace(' ', '_', $search), '_');

        $queryParams = [
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => 'intitle:' . $searchstring,
            'srnamespace' => '0',
            'srlimit' => $limit
        ];

        $queryString = '?' . http_build_query($queryParams);

        $result = $this->makeRequest('GET', $queryString);

        if ($result && isset($result->query->searchinfo->totalhits) && $result->query->searchinfo->totalhits > 0) {
            return $result->query->search;
        }

        return [];
    }

    /**
     * Get an article extract from Wikipedia
     *
     * @param string $title Title of the article
     * @return object|null Article object with ->title, ->extract
     */
    public function getArticle(string $title): ?object
    {
        $title = trim(str_replace(' ', '_', $title), '_');

        $queryParams = [
            'action' => 'query',
            'titles' => $title,
            'format' => 'json',
            'prop' => 'extracts',
            'exintro' => '',
            'explaintext' => '',
            'redirects' => '1',
            'indexpageids' => ''
        ];

        $queryString = '?' . http_build_query($queryParams);
        $result = $this->makeRequest('GET', $queryString);

        if ($result && isset($result->query->pages)) {
            foreach ($result->query->pages as $article) {
                return $article;
            }
        }

        return null;
    }
}
