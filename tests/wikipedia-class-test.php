<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KraenzleRitter\ResourcesComponents\Wikipedia;

// Hilfsfunktionen, um das Laravel-Framework zu simulieren
if (!function_exists('config')) {
    function config($path) {
        global $config;
        $keys = explode('.', $path);
        $temp = $config;

        foreach ($keys as $key) {
            if (!isset($temp[$key])) {
                return null;
            }
            $temp = $temp[$key];
        }

        return $temp;
    }
}

// Unsere Testkonfiguration
$config = [
    'resources-components' => [
        'providers' => [
            'wikipedia-de' => [
                'base_url' => 'https://de.wikipedia.org/w/api.php'
            ],
            'wikipedia-en' => [
                'base_url' => 'https://en.wikipedia.org/w/api.php'
            ],
            'wikipedia-fr' => [
                'base_url' => 'https://fr.wikipedia.org/w/api.php'
            ]
        ]
    ]
];

// Hilfsklasse für Logging
if (!class_exists('Log')) {
    class Log {
        public static function debug($message, $context = []) {
            echo "DEBUG: $message\n";
            if (!empty($context)) {
                echo "Context: " . print_r($context, true) . "\n";
            }
        }

        public static function warning($message) {
            echo "WARNING: $message\n";
        }
    }
}

// Funktion, um unsere Wikipedia-Klasse zu testen
function testWikipediaClass($providerKey, $searchTerm) {
    echo "\n\n=== Test Wikipedia-Klasse mit Provider: $providerKey ===\n";

    $wiki = new Wikipedia();
    $results = $wiki->search($searchTerm, ['providerKey' => $providerKey, 'limit' => 2]);

    echo "Suchergebnisse für '$searchTerm':\n";

    if (empty($results)) {
        echo "Keine Ergebnisse gefunden.\n";
        return;
    }

    foreach ($results as $result) {
        echo "- Titel: {$result->title}\n";
        echo "  Snippet: " . strip_tags($result->snippet) . "\n\n";

        // Get full article for first result
        if ($result === reset($results)) {
            echo "Artikel-Details:\n";
            $article = $wiki->getArticle($result->title, ['providerKey' => $providerKey]);

            if ($article && isset($article->extract)) {
                echo "Extract (erste 150 Zeichen):\n";
                echo substr($article->extract, 0, 150) . "...\n";
            } else {
                echo "Artikel konnte nicht abgerufen werden.\n";
            }
        }
    }
}

// Test verschiedene Sprachen
$searchTerm = 'Albert Einstein';
testWikipediaClass('wikipedia-de', $searchTerm);
testWikipediaClass('wikipedia-en', $searchTerm);
testWikipediaClass('wikipedia-fr', $searchTerm);
