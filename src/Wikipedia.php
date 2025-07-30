<?php

namespace KraenzleRitter\ResourcesComponents;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7;

/**
 * Wikipedia queries
 *
 *  Use the Wikipedia API
 *  (new Wiki())->search('Karl Barth');
 *  (new Wiki())->getArticle('Karl Barth'); // you need the exact title
 *
 */
class Wikipedia
{
    public $client;

    /**
     * Sets the client for the specified locale
     *
     * @param string $locale The language to use (e.g. 'de', 'en')
     * @return void
     */
    private function setClientForLocale($locale = 'de')
    {
        $base_uri = "https://{$locale}.wikipedia.org/w/api.php";
        try {
            $this->client = new Client(['base_uri' => $base_uri]);
        } catch (\Exception $e) {
            // Log error when creating the client
            error_log($e->getMessage());
        }
    }

    /**
     * Get a Wikipedia article list
     * @param  string $searchstring
     * @param  array $params possible keys: limit, locale
     * @return array|null Array of objects with $entry->title, strip_tags($entry->snippet) or null on error
     */
    public function search($searchstring, $params)
    {
        $limit = $params['limit'] ?? 5;
        $locale = $params['locale'] ?? 'de';

        // Debug output to see which locale is actually being used
        \Log::debug('Wikipedia search called with locale: ' . $locale . ', search: ' . $searchstring);
        \Log::debug('Params: ', $params);

        // Check if we have a providerKey and can read the API URL from the configuration
        $providerKey = 'wikipedia-' . $locale;
        $apiUrl = config('resources-components.providers.' . $providerKey . '.base_url');

        if ($apiUrl) {
            \Log::debug('Using API URL from config: ' . $apiUrl);
            // Create client directly with the configured URL
            $this->client = new Client(['base_uri' => $apiUrl]);
        } else {
            // Fallback: Create client with URL determined by locale
            \Log::debug('No config found for provider ' . $providerKey . ', using locale to build URL');
            $this->setClientForLocale($locale);
        }        $searchstring = trim(str_replace(' ', '_', $searchstring), '_');
        $query = [];
        $query[] = 'action=query';
        $query[] = 'format=json';
        $query[] = 'list=search';
        $query[] = 'srsearch=intitle:' . $searchstring;
        $query[] = 'srnamespace=0';
        $query[] = 'srlimit=' . $limit;

        try {
            $response = $this->client->get('?' . join('&', $query));
            $body = json_decode($response->getBody());

            if (isset($body->query->searchinfo->totalhits) && $body->query->searchinfo->totalhits > 0) {
                return $body->query->search;
            }
        } catch (RequestException $e) {
            // Log error during API call
            error_log(Psr7\Message::toString($e->getRequest()));
            if ($e->hasResponse()) {
                error_log(Psr7\Message::toString($e->getResponse()));
            }
        }

        return [];
    }

    /**
     * Get an article extract from Wikipedia
     * @param  string $title Title of the article
     * @param  array $params Possible keys: locale
     * @return object|null   Object with ->title, ->extract or null on error
     */
    public function getArticle($title, $params = [])
    {
        $locale = $params['locale'] ?? 'de';

        // Debug output
        \Log::debug('Wikipedia getArticle called with locale: ' . $locale . ', title: ' . $title);

        // Check if we have a providerKey and can read the API URL from the configuration
        $providerKey = 'wikipedia-' . $locale;
        $apiUrl = config('resources-components.providers.' . $providerKey . '.base_url');

        if ($apiUrl) {
            \Log::debug('Using API URL from config: ' . $apiUrl);
            // Create client directly with the configured URL
            $this->client = new Client(['base_uri' => $apiUrl]);
        } else {
            // Fallback: Create client with URL determined by locale
            \Log::debug('No config found for provider ' . $providerKey . ', using locale to build URL');
            $this->setClientForLocale($locale);
        }

        $title = trim(str_replace(' ', '_', $title), '_');
        $query = [];
        $query[] = 'action=query';
        $query[] = 'titles=' . $title;
        $query[] = 'format=json';
        $query[] = 'prop=extracts';
        $query[] = 'exintro';
        $query[] = 'explaintext';
        $query[] = 'redirects=1';
        $query[] = 'indexpageids';

        try {
            $response = $this->client->get('?' . join('&', $query));
            $body = json_decode($response->getBody());

            if (isset($body->query->pages)) {
                foreach ($body->query->pages as $article) {
                    return $article;
                }
            }
        } catch (RequestException $e) {
            // Log error during API call
            error_log(Psr7\Message::toString($e->getRequest()));
            if ($e->hasResponse()) {
                error_log(Psr7\Message::toString($e->getResponse()));
            }
        }

        // If no article was found or an error occurred
        return null;
    }
}
