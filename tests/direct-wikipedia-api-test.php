<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

// Test direkt verschiedene Wikipedia-URLs, um die Sprachausgabe zu überprüfen
function testWikipediaApi($locale, $searchTerm) {
    echo "\n=== Test Wikipedia API für Sprache: $locale ===\n";

    $apiUrl = "https://{$locale}.wikipedia.org/w/api.php";
    $client = new Client(['base_uri' => $apiUrl]);

    // Suchabfrage zusammenstellen
    $query = [
        'action' => 'query',
        'format' => 'json',
        'list' => 'search',
        'srsearch' => "intitle:" . $searchTerm,
        'srnamespace' => 0,
        'srlimit' => 2
    ];

    try {
        echo "Abfrage an: $apiUrl\n";
        $response = $client->get('?' . http_build_query($query));
        $body = json_decode($response->getBody());

        if (isset($body->query->search) && count($body->query->search) > 0) {
            echo "Ergebnisse für '$searchTerm':\n";

            foreach ($body->query->search as $result) {
                echo "- Titel: {$result->title}\n";
                echo "  Snippet: " . strip_tags($result->snippet) . "\n\n";

                // Für das erste Ergebnis den kompletten Artikel abrufen
                if ($result === reset($body->query->search)) {
                    $articleQuery = [
                        'action' => 'query',
                        'titles' => $result->title,
                        'format' => 'json',
                        'prop' => 'extracts',
                        'exintro' => true,
                        'explaintext' => true,
                        'redirects' => 1,
                        'indexpageids' => true
                    ];

                    $articleResponse = $client->get('?' . http_build_query($articleQuery));
                    $articleBody = json_decode($articleResponse->getBody());

                    if (isset($articleBody->query->pages)) {
                        $article = reset($articleBody->query->pages);

                        if (isset($article->extract)) {
                            echo "Artikel-Extract (erste 150 Zeichen):\n";
                            echo substr($article->extract, 0, 150) . "...\n\n";
                        } else {
                            echo "Kein Artikelextrakt verfügbar.\n\n";
                        }
                    }
                }
            }
        } else {
            echo "Keine Ergebnisse gefunden.\n";
        }
    } catch (Exception $e) {
        echo "Fehler beim API-Zugriff: " . $e->getMessage() . "\n";
    }
}

// Test verschiedene Sprachen
$searchTerm = 'Albert Einstein';
testWikipediaApi('de', $searchTerm);
testWikipediaApi('en', $searchTerm);
testWikipediaApi('fr', $searchTerm);
