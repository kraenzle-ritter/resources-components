<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Language Wikipedia Configuration
    |--------------------------------------------------------------------------
    */
    'multilanguage-wikipedia' => [
        'enabled' => true,
        'default_language' => 'de',
        'default_languages' => ['de', 'en'], // For multi-language searches
        'limit' => 5,
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Instance Anton Configuration
    |--------------------------------------------------------------------------
    */
    'multi-instance-anton' => [
        'enabled' => true,
        'default_instance' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Anton Instances Configuration
    |--------------------------------------------------------------------------
    | Configure multiple Anton implementations with different URLs and tokens
    */
    'anton' => [
        'instances' => [
            'default' => [
                'name' => 'Standard Anton Instance',
                'api_url' => env('ANTON_DEFAULT_API_URL', 'https://api.anton.ch'),
                'token' => env('ANTON_DEFAULT_TOKEN', ''),
                'limit' => 5,
                'enabled' => true,
                'description' => 'Main Anton instance for general searches'
            ],
            
            'cultural-heritage' => [
                'name' => 'Cultural Heritage Anton',
                'api_url' => env('ANTON_CULTURAL_API_URL', 'https://cultural.anton.ch'),
                'token' => env('ANTON_CULTURAL_TOKEN', ''),
                'limit' => 10,
                'enabled' => env('ANTON_CULTURAL_ENABLED', false),
                'description' => 'Specialized instance for cultural heritage data'
            ],
            
            'archival' => [
                'name' => 'Archival Anton',
                'api_url' => env('ANTON_ARCHIVAL_API_URL', 'https://archives.anton.ch'),
                'token' => env('ANTON_ARCHIVAL_TOKEN', ''),
                'limit' => 8,
                'enabled' => env('ANTON_ARCHIVAL_ENABLED', false),
                'description' => 'Archive-specific Anton instance'
            ],
            
            'library' => [
                'name' => 'Library Anton',
                'api_url' => env('ANTON_LIBRARY_API_URL', 'https://library.anton.ch'),
                'token' => env('ANTON_LIBRARY_TOKEN', ''),
                'limit' => 6,
                'enabled' => env('ANTON_LIBRARY_ENABLED', false),
                'description' => 'Library catalog Anton instance'
            ],
            
            'museum' => [
                'name' => 'Museum Anton',
                'api_url' => env('ANTON_MUSEUM_API_URL', 'https://museum.anton.ch'),
                'token' => env('ANTON_MUSEUM_TOKEN', ''),
                'limit' => 7,
                'enabled' => env('ANTON_MUSEUM_ENABLED', false),
                'description' => 'Museum collections Anton instance'
            ]
        ],

        // Legacy configuration for backward compatibility
        'api_url' => env('ANTON_API_URL', 'https://api.anton.ch'),
        'token' => env('ANTON_TOKEN', ''),
        'limit' => 5
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Factory Registration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'multilanguage-wikipedia' => \KraenzleRitter\ResourcesComponents\Providers\MultiLanguageWikipediaProvider::class,
        'multi-instance-anton' => \KraenzleRitter\ResourcesComponents\Providers\MultiInstanceAntonProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'resources_components',
        'store' => env('CACHE_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'limit' => 5,
        'timeout' => 30,
        'retry_attempts' => 3,
    ],
];
