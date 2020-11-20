<?php

return [
    'geonames' => [
        'username' => env('GEONAMES_USERNAME', 'demo'),
        //'continent-code' => 'EU', // Restricts the search for toponym of the given continent
        //'countryBias' => 'CH',    // Records from the countryBias are listed first
        'limit' => 5, // Default is 5, the maximal allowed value is 1000.

    ],
    'gnd' => [
        'limit' => 5
    ],
    'anton' => [
        'provider-slug' => env('ANTON_PROVIDER_SLUG', 'anton'),
        'token' => env('ANTON_API_TOKEN', 'test'),
        'url' => env('ANTON_URL', 'https://kr.anton.ch'),
        'api_url' => env('ANTON_API_URL', 'https://kr.anton.ch/api'),
        'limit' => 5
    ],
    'metagrid' => [
        'limt' => 5
    ],
    'wikipedia' => [
        'limit' => 5
    ],
    'wikidata' => [
        'limit' => 5,
        'locale' => 'de'
    ],

];
