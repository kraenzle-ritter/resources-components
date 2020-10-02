<?php

namespace KraenzleRitter\ResourcesComponents;

use \GuzzleHttp\Client;

/**
 * Wikipedia queries
 *
 *  use the wikipedia api
 *  (new Wiki())->search('Karl Barth');
 *  (new Wiki())->getArticle('Karl Barth'); // you need the exact title
 *
 */
class Wikipedia
{
    public $client;

    /**
     * get a wikipedia article list
     * @param  string $searchstring
     * @param  array $params possible keys: limit, locale
     * @return array of objects  $entry->title, strip_tags($entry->snippet)
     */
    public function search($searchstring, $params)
    {
        $limit = $params['limit'] ?? 5;
        $locale = $params['locale'] ?? 'de';

        $base_uri = "https://{$locale}.wikipedia.org/w/api.php";
        try {
            $this->client = new Client(['base_uri' => $base_uri]);
        } catch (Exception $e) {
            print($e->message);
        }

        $searchstring = trim(str_replace(' ', '_', $searchstring), '_');
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
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        if ($body->query->searchinfo->totalhits > 0)
        {
            return $body->query->search;
        }

        return null;
    }

    /**
     * get an article extract from wikipedia
     * @param  string $title title of the article
     * @return object        ->title, ->extract
     */
    public function getArticle($title)
    {
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
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        $body = json_decode($response->getBody());

        foreach ($body->query->pages as $article) {
            return $article;
        }
    }
}
