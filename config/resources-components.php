<?php

return [
    'limit' => 5,
    'providers' => [
       'gnd' => [
            'label' => 'GND',
            'api-type' => 'Gnd',
            'base_url' => 'https://lobid.org/gnd/',
        ],
        'geonames' => [
            'label' => 'Geonames',
            'api-type' => 'Geonames',
            'base_url' => 'http://api.geonames.org/',
            'user_name' => env('GEONAMES_USERNAME', 'demo')
            // 'continent-code' => 'EU', // Restricts the search for toponym of the given continent
            // 'countryBias' => 'CH',    // Records from the countryBias are listed first
        ],
        'georgfischer' => [
            'label' => 'Konzernarchiv der Georg Fischer AG',
            'api-type' => 'Anton',
            'base_url' => 'https://archives.georgfischer.com/api/',
            'api_token' => env('GEORGFISCHER_API_TOKEN', ''),
        ],
        'gosteli' => [
            'label' => 'Gosteli-Archiv',
            'api-type' => 'Anton',
            'base_url' => 'https://gosteli.anton.ch/api/',
            'api_token' => env('GOSTELI_API_TOKEN', ''),
        ],
        'idiotikon' => [
            'label' => 'Idiotikon',
            'api-type' => 'Idiotikon',
            'base_url' => 'https://api.idiotikon.ch/',
        ],
        'kba' => [
            'label' => 'Karl Barth-Archiv',
            'api-type' => 'Anton',
            'base_url' => 'https://kba.karl-barth.ch/api/',
            'api_token' => env('KBA_API_TOKEN', ''),
        ],
        'manual-input' => [
            'api-type' => 'ManualInput',
        ],
        'metagrid' => [
            'label' => 'Metagrid',
            'api-type' => 'Metagrid',
            'base_url' => 'https://metagrid.ch/api/',
        ],
        'ortsnamen' => [
            'label' => 'Ortsnamen',
            'api-type' => 'Ortsnamen',
            'base_url' => 'https://search.ortsnamen.ch/de/api/'
        ],
        'wikidata' => [
            'label' => 'Wikidata',
            'api-type' => 'Wikidata',
            'base_url' => 'https://www.wikidata.org/w/api.php',
        ],
        'wikipedia-de' => [
            'label' => 'Wikipedia (de)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://de.wikipedia.org/w/api.php',
        ],
        'wikipedia-en' => [
            'label' => 'Wikipedia (en)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://en.wikipedia.org/w/api.php',
        ],
        'wikipedia-fr' => [
            'label' => 'Wikipedia (fr)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://fr.wikipedia.org/w/api.php',
        ],
        'wikipedia-it' => [
            'label' => 'Wikipedia (it)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://it.wikipedia.org/w/api.php',
        ],
    ]
];
