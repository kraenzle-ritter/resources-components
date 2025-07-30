<?php

namespace KraenzleRitter\ResourcesComponents\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use KraenzleRitter\ResourcesComponents\Helpers\TextHelper;

class WikipediaProvider extends AbstractProvider
{
    /**
     * HTTP client for API requests
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Language code for Wikipedia
     *
     * @var string
     */
    protected $locale;

    /**
     * Create a new Wikipedia provider instance
     *
     * @param string $providerKey The provider key from config
     * @param array $config The provider configuration
     */
    public function __construct(string $providerKey, array $config = [])
    {
        parent::__construct($providerKey, $config);

        // Extract locale from providerKey (e.g., 'wikipedia-en' => 'en')
        $this->locale = isset($providerKey) && strpos($providerKey, '-') !== false
            ? explode('-', $providerKey)[1]
            : 'de';

        $base_uri = "https://{$this->locale}.wikipedia.org/w/api.php";

        try {
            $this->client = new Client(['base_uri' => $base_uri]);
        } catch (\Exception $e) {
            // Log error when creating the client
            error_log($e->getMessage());
        }
    }

    /**
     * Search for articles on Wikipedia
     *
     * @param string $search The search query
     * @param array $params Additional parameters
     * @return array|null The search results or null on error
     */
    public function search(string $search, array $params = [])
    {
        $limit = $params['limit'] ?? 5;

        try {
            $response = $this->client->request('GET', '', [
                'query' => [
                    'action' => 'query',
                    'format' => 'json',
                    'list' => 'search',
                    'srsearch' => $search,
                    'srlimit' => $limit,
                    'utf8' => '1',
                    'srprop' => 'snippet'
                ]
            ]);

            $data = json_decode($response->getBody());

            return $data->query->search ?? null;
        } catch (RequestException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Process Wikipedia search results into a standardized format
     *
     * @param mixed $results The results from the search method
     * @return array Standardized array of results
     */
    public function processResult($results): array
    {
        if (!$results) {
            return [];
        }

        $processed = [];
        $textHelper = new TextHelper();

        foreach ($results as $result) {
            $title = $result->title;
            $snippet = strip_tags($result->snippet);
            $description = $textHelper->extractFirstSentence($snippet);
            $url = "https://{$this->locale}.wikipedia.org/wiki/" . urlencode(str_replace(' ', '_', $title));

            $processed[] = [
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'raw_data' => json_encode($result)
            ];
        }

        return $processed;
    }

    /**
     * Get full article data by title
     *
     * @param string $title The exact article title
     * @return object|null The article data or null on error
     */
    public function getArticle(string $title)
    {
        try {
            $response = $this->client->request('GET', '', [
                'query' => [
                    'action' => 'query',
                    'format' => 'json',
                    'prop' => 'extracts',
                    'exintro' => '1',
                    'explaintext' => '1',
                    'titles' => $title,
                    'utf8' => '1'
                ]
            ]);

            $data = json_decode($response->getBody());
            $pages = $data->query->pages ?? null;

            if ($pages) {
                return reset($pages); // Return the first page
            }

            return null;
        } catch (RequestException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
