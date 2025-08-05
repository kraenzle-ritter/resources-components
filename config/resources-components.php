<?php

return [
    'limit' => 5,
    'providers' => [
        'gnd' => [
            'label' => 'GND',
            'api-type' => 'Gnd',
            'base_url' => 'https://lobid.org/gnd/',
            'target_url' => 'https://d-nb.info/gnd/{provider_id}', // For saved links
            'test_search' => 'Hannah Arendt',
        ],
        'geonames' => [
            'label' => 'Geonames',
            'api-type' => 'Geonames',
            'base_url' => 'http://api.geonames.org/',
            'user_name' => env('GEONAMES_USERNAME', 'demo'),
            // Standardized configuration keys with underscores:
            'continent_code' => null, // Restricts the search for toponym of the given continent
            'country_bias' => null,   // Records from the countryBias are listed first
            'target_url' => 'https://www.geonames.org/{provider_id}',
            'test_search' => 'Augsburg',
        ],
        'georgfischer' => [
            'label' => 'Konzernarchiv der Georg Fischer AG',
            'api-type' => 'Anton',
            'base_url' => 'https://archives.georgfischer.com/api/',
            'api_token' => env('GEORGFISCHER_API_TOKEN', ''),
            'target_url' => 'https://archives.georgfischer.com/{endpoint}/{short_provider_id}',
            'slug' => 'gfa',
            'test_search' => 'Georg Fischer',
        ],
        'gosteli' => [
            'label' => 'Gosteli-Archiv',
            'api-type' => 'Anton',
            'base_url' => 'https://gosteli.anton.ch/api/',
            'api_token' => env('GOSTELI_API_TOKEN', ''),
            'target_url' => 'https://gosteli.anton.ch/api/{endpoint}/{short_provider_id}',
            'slug' => 'gosteli',
            'test_search' => 'Marthe Gosteli',
        ],
        'idiotikon' => [
            'label' => 'Idiotikon',
            'api-type' => 'Idiotikon',
            'base_url' => 'https://api.idiotikon.ch/',
            'target_url' => 'https://digital.idiotikon.ch/p/lem/{provider_id}',
            'test_search' => 'Allmend',
        ],
        'kba' => [
            'label' => 'Karl Barth-Archiv',
            'api-type' => 'Anton',
            'base_url' => 'https://kba.karl-barth.ch/api/',
            'api_token' => env('KBA_API_TOKEN', ''),
            'target_url' => 'https://kba.karl-barth.ch/api/{endpoint}/{short_provider_id}',
            'slug' => 'kba',
            'test_search' => 'Karl Barth',
        ],
        'manual-input' => [
            'api-type' => 'ManualInput',
        ],
        'metagrid' => [
            'label' => 'Metagrid',
            'api-type' => 'Metagrid',
            'base_url' => 'https://metagrid.ch/api/',
            'target_url' => 'https://api.metagrid.ch/concordance/{provider_id}.json', // since metagrid has no Gui for these entries
            'test_search' => 'Anna Tumarkin',
        ],
        'ortsnamen' => [
            'label' => 'Ortsnamen',
            'api-type' => 'Ortsnamen',
            'base_url' => 'https://search.ortsnamen.ch/de/api/',
            'target_url' => 'https://search.ortsnamen.ch/de/record/{provider_id}',
            'test_search' => 'Wiedikon'
        ],
        'wikidata' => [
            'label' => 'Wikidata',
            'api-type' => 'Wikidata',
            'base_url' => 'https://www.wikidata.org/w/api.php',
            'target_url' => 'https://www.wikidata.org/wiki/{provider_id}',
            'test_search' => 'Lucretia Marinella',
        ],
        'wikipedia-de' => [
            'label' => 'Wikipedia (de)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://de.wikipedia.org/w/api.php',
            'target_url' => 'https://de.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Bertha von Suttner',
        ],
        'wikipedia-en' => [
            'label' => 'Wikipedia (en)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://en.wikipedia.org/w/api.php',
            'target_url' => 'https://en.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Mary Astell',
        ],
        'wikipedia-fr' => [
            'label' => 'Wikipedia (fr)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://fr.wikipedia.org/w/api.php',
            'target_url' => 'https://fr.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Marie de Gournay',
        ],
        'wikipedia-it' => [
            'label' => 'Wikipedia (it)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://it.wikipedia.org/w/api.php',
            'target_url' => 'https://it.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Laura Bassi',
        ],
        'bnf' => [
            'label' => 'Bibliothèque nationale de France (BnF)',
        ],
        'bsg' => [
            'label' => 'Bibliographie der Schweizergeschichte (BSG)',
        ],
        'burgerbibliothek' => [
            'label' => 'Burgerbibliothek Bern',
        ],
        'ddb' => [
            'label' => 'Deutsche Digitale Bibliothek (DDB)',
        ],
        'deutsche-biographie' => [
            'label' => 'Deutsche Biographie',
        ],
        'encyclopaedia-britannica-online' => [
            'label' => 'Encyclopaedia Britannica Online',
        ],
        'histhub' => [
            'label' => 'Histhub',
        ],
        'hls-dhs-dss' => [
            'label' => 'Historisches Lexikon der Schweiz (HLS/DHS/DSS)',
        ],
        'kalliope-verbund' => [
            'label' => 'Kalliope Verbund',
        ],
        'kbga' => [
            'label' => 'Karl Barth-Gesamtausgabe (KBGA)',
        ],
        'lcnaf' => [
            'label' => 'Library of Congress (LCNAF)'
        ],
        'rag' => [
            'label' => 'RAG (Repertorium Academicum Germanicum)',
        ],
        'sbn' => [
            'label' => 'SBN (Servicio Bibliotecario Nazionale)',
        ],
        'ssrq' => [
            'label' => 'Sammlung Schweizerischer Rechtsquellen',
        ],
        'viaf' => [
            'label' => 'VIAF'
        ],

    ]
];
