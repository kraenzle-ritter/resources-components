<?php

return [
    'limit' => 5,
    'providers' => [
        'gnd' => [
            'label' => 'GND', // not localized, as it is a standard identifier
            'api-type' => 'Gnd',
            'base_url' => 'https://lobid.org/gnd/',
            'target_url' => 'https://d-nb.info/gnd/{provider_id}', // For saved links
            'test_search' => 'Hannah Arendt',
        ],
        'geonames' => [
            'label' => "GeoNames", // not localized as it is a standard identifier
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
            'label' => [
                'de' => 'Konzernarchiv der Georg Fischer AG',
                'en' => 'Corporate Archives of Georg Fischer Ltd',
            ],
            'api-type' => 'Anton',
            'base_url' => 'https://archives.georgfischer.com/api/',
            'api_token' => env('GEORGFISCHER_API_TOKEN', ''),
            'target_url' => 'https://archives.georgfischer.com/{endpoint}/{short_provider_id}',
            'slug' => 'gfa',
            'test_search' => 'Georg Fischer',
        ],
        'gosteli' => [
            'label' => [
                'de' => 'Gosteli Archiv',
                'en' => 'Gosteli Archive',
                'fr' => 'Archives Gosteli',
                'it' => 'Archivio Gosteli',
            ],
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
            'label' => [
                'de' => 'Karl Barth-Archiv',
                'en' => 'Karl Barth Archive',
                'fr' => 'Archives Karl Barth',
                'it' => 'Archivio Karl Barth',
            ],
            'api-type' => 'Anton',
            'base_url' => 'https://kba.karl-barth.ch/api/',
            'api_token' => env('KBA_API_TOKEN', ''),
            'target_url' => 'https://kba.karl-barth.ch/api/{endpoint}/{short_provider_id}',
            'slug' => 'kba',
            'test_search' => 'Karl Barth',
        ],
        'manual-input' => [
            'label' => [
                'en' => 'Manual Input',
                'de' => 'Manuelle Eingabe',
                'fr' => 'Saisie manuelle',
                'it' => 'Inserimento manuale',
            ],
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
            'label' => 'ortsnamen.ch',
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
            'target_url' => 'https://en.wikipedia.org/wiki/{provider_id}',
            'test_search' => 'Lucretia Marinella',
        ],
        'wikipedia-fr' => [
            'label' => 'Wikipedia (fr)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://fr.wikipedia.org/w/api.php',
            'target_url' => 'https://fr.wikipedia.org/wiki/{provider_id}',
            'test_search' => 'Lucretia Marinella',
        ],
        'wikipedia-it' => [
            'label' => 'Wikipedia (it)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://it.wikipedia.org/w/api.php',
            'target_url' => 'https://it.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Laura Bassi',
        ],
        'alfred-escher' => [
            'label' => 'Alfred Escher Briefedition',
        ],
        'bnf' => [
            'label' => 'Bibliothèque nationale de France (BnF)',
        ],
        'bsg' => [
            'label' => [
                'de' => 'Bibliographie der Schweizergeschichte (BSG)',
                'en' => 'Bibliography of Swiss History (BSH)',
                'fr' => 'Bibliographie de l‘histoire suisse (BHS)',
                'it' => 'Bibliografia della storia svizzera (BSS)',
            ],
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
        'dodis' => [
            'label' => 'Dodis'
        ],
        'elites-suisses-au-xxe-siecle' => [
            'label' => 'Elites suisses au XXème siècle',
        ],
        'encyclopaedia-britannica-online' => [
            'label' => 'Encyclopaedia Britannica Online',
        ],
        'ethz' => [
            'label' => [
                'de' => 'ETH Zürich (Hochschularchiv)',
                'en' => 'ETH Zurich (University Archives)'
            ],
        ],
        'fotostiftung' => [
            'label' => 'Fotostiftung Schweiz',
        ],
        'hallernet' => [
            'label' => 'HallerNet',
        ],
        'helveticat' => [
            'label' =>  'helveticat',
        ],
        'histhub' => [
            'label' => 'Histhub',
        ],
        'histoirerurale' => [
            'label' => [
                'de' => 'Archiv für Argrargeschichte',
                'en' => 'Archives of rural history',
                'fr' => 'Archives de l‘histoire rurale',
            ],

        ],
        'hls-dhs-dss' => [
            'label' => [
                'de' => 'Historisches Lexikon der Schweiz (HLS)',
                'en' => 'Historical Dictionary of Switzerland',
                'fr' => 'Dictionnaire historique de la Suisse (DHS)',
                'it' => 'Dizionario storico della Svizzera (DSS)',
            ],
        ],
        'kalliope-verbund' => [
            'label' => 'Kallipope Verbund',
        ],
        'kbga' => [
            'label' => 'Karl Barth-Gesamtausgabe (KBGA)',
        ],
        'lcnaf' => [
            'label' => 'Library of Congress (LCNAF)',
        ],
        'rag' => [
            'label' => 'RAG (Repertorium Academicum Germanicum)',
        ],
        'sbn' => [
            'label' => 'SBN (Servizio Bibliotecario Nazionale)',
        ],
        'ssrq' => [
            'label' => [
                'de' => 'Schweizerischer Rechtsquellen (SSRQ)',
                'en' => 'Swiss Legal Sources (SLS)',
                'fr' => 'Les sources du droit suisse (SDS)',
                'it' => 'Fonti del diritto svizzero (FDS)',
            ],
        ],
        'viaf' => [
            'label' => 'VIAF (Virtual International Authority File)',
        ],
        'worldcat' => [
            'label' => 'WorldCat',
        ]

    ]
];
