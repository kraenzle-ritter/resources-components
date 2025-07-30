<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Einfacher Test für die Wikipedia URL-Generierung

// 1. Test mit API-URLs
$apiUrls = [
    'https://de.wikipedia.org/w/api.php',
    'https://en.wikipedia.org/w/api.php',
    'https://fr.wikipedia.org/w/api.php',
    'https://it.wikipedia.org/w/api.php'
];

echo "=== Test: API-URLs zu Frontend-URLs konvertieren ===\n";
foreach ($apiUrls as $apiUrl) {
    $frontendUrl = str_replace('/w/api.php', '/wiki/', $apiUrl);
    echo "API-URL:     {$apiUrl}\n";
    echo "Frontend-URL: {$frontendUrl}\n\n";
}

// 2. Test mit Provider-Keys
$providerKeys = [
    'wikipedia-de',
    'wikipedia-en',
    'wikipedia-fr',
    'wikipedia-it'
];

echo "=== Test: Provider-Keys zu URLs konvertieren ===\n";
foreach ($providerKeys as $providerKey) {
    $locale = substr($providerKey, strlen('wikipedia-'));
    $apiUrl = "https://{$locale}.wikipedia.org/w/api.php";
    $frontendUrl = str_replace('/w/api.php', '/wiki/', $apiUrl);

    echo "Provider-Key: {$providerKey}\n";
    echo "Locale:       {$locale}\n";
    echo "API-URL:      {$apiUrl}\n";
    echo "Frontend-URL: {$frontendUrl}\n\n";
}
