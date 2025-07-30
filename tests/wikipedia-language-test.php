<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KraenzleRitter\ResourcesComponents\Wikipedia;

// Definieren Sie eine einfache Log-Klasse für Tests
if (!class_exists('\Log')) {
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

// Testkonfiguration für verschiedene Wikipedia-Provider setzen
$testConfig = [
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

// Mock für die Laravel Config-Funktion
if (!function_exists('config')) {
    function config($key) {
        global $testConfig;

        $parts = explode('.', $key);
        $config = $testConfig;

        foreach ($parts as $part) {
            if (isset($config[$part])) {
                $config = $config[$part];
            } else {
                return null;
            }
        }

        return $config;
    }
}

// Test verschiedene Sprachen
$searchTerm = 'Albert Einstein';
$languages = ['wikipedia-de', 'wikipedia-en', 'wikipedia-fr'];

foreach ($languages as $language) {
    echo "\n\n=== Teste Wikipedia mit Provider: $language ===\n";

    $wiki = new Wikipedia();
    $results = $wiki->search($searchTerm, ['providerKey' => $language, 'limit' => 2]);

    echo "Suchergebnisse für '$searchTerm':\n";

    if (empty($results)) {
        echo "Keine Ergebnisse gefunden.\n";
        continue;
    }

    foreach ($results as $result) {
        echo "- Titel: {$result->title}\n";
        echo "  Snippet: " . strip_tags($result->snippet) . "\n";

        // Get full article for first result
        if ($result === reset($results)) {
            echo "\nArtikel-Details:\n";
            $article = $wiki->getArticle($result->title, ['providerKey' => $language]);

            if ($article) {
                echo "Titel: {$article->title}\n";
                echo "Extract (erste 150 Zeichen): " .
                     substr($article->extract, 0, 150) . "...\n";
            } else {
                echo "Artikel konnte nicht abgerufen werden.\n";
            }
        }
    }
}
