# Resources Components Package

A Laravel package for integrating various external data sources to search for persons, organizations, and places with comprehensive multi-language and multi-instance support.

## 🌟 Features

### Core Providers
- **GND**: Gemeinsame Normdatei (persons, organizations, places)
- **Wikidata**: Structured data from Wikipedia
- **Wikipedia**: Wikipedia articles with multi-language support
- **Geonames**: Geographic database
- **Metagrid**: Metadata aggregator for Swiss archives
- **Idiotikon**: Swiss German dictionary
- **Ortsnamen**: Swiss place names database
- **Anton**: Customizable provider with multi-instance support

### Advanced Capabilities
- **Multi-Language Wikipedia**: Search across 19 Wikipedia language versions
- **Multi-Instance Anton**: Configure multiple Anton APIs with different endpoints
- **Livewire Components**: Ready-to-use UI components with error handling
- **Comprehensive Error Handling**: Prevents page crashes from API failures
- **Automatic Caching**: Configurable TTL and cache management
- **Factory Pattern**: Easy provider registration and creation
- **Extensible Architecture**: Abstract base classes for new providers
- **Complete Testing**: PHPUnit test suite with mock support

## 🚀 Quick Start

### Installation

```bash
composer require kraenzle-ritter/resources-components
```

### Basic Usage

```php
// In your Blade template
<livewire:gnd-lw-component :model="$yourModel" search="Einstein" />
<livewire:wikidata-lw-component :model="$yourModel" search="Zurich" />

// Multi-Language Wikipedia
<livewire:multi-language-wikipedia-lw-component 
    :model="$yourModel" 
    search="Einstein" 
    :languages="['de', 'en', 'fr']" 
/>

// Multi-Instance Anton
<livewire:multi-instance-anton-lw-component 
    :model="$yourModel" 
    search="Archive"
    instance="cultural-heritage"
    endpoint="objects"
/>
```

### Programmatic Usage

```php
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

// Basic provider usage
$provider = ProviderFactory::create('gnd');
$results = $provider->search('Einstein', ['limit' => 10]);

// Multi-language Wikipedia
$wikipedia = ProviderFactory::create('multilanguage-wikipedia');
$results = $wikipedia->search('Einstein', [
    'languages' => ['de', 'en', 'fr'],
    'limit' => 5
]);

// Multi-instance Anton
$anton = ProviderFactory::create('multi-instance-anton');
$anton->setInstance('cultural-heritage');
$results = $anton->search('Archive', ['size' => 10], 'objects');
```

## ⚙️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="resources-components"
```

### Basic Configuration

```php
// config/resources-components.php
return [
    'providers' => [
        'gnd' => [
            'enabled' => true,
            'limit' => 5,
            'timeout' => 30,
        ],
        'geonames' => [
            'enabled' => true,
            'username' => env('GEONAMES_USERNAME'),
            'limit' => 5,
        ],
        'anton' => [
            'enabled' => true,
            'api_url' => env('ANTON_API_URL'),
            'token' => env('ANTON_TOKEN'),
            'limit' => 5,
        ],
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
];
```

### Multi-Provider Configuration

```php
// config/multi-providers.php
return [
    'multilanguage-wikipedia' => [
        'enabled' => true,
        'default_language' => 'de',
        'default_languages' => ['de', 'en'],
    ],
    
    'anton' => [
        'instances' => [
            'default' => [
                'name' => 'Standard Anton',
                'api_url' => env('ANTON_DEFAULT_API_URL'),
                'token' => env('ANTON_DEFAULT_TOKEN'),
                'enabled' => true,
            ],
            'cultural-heritage' => [
                'name' => 'Cultural Heritage Anton',
                'api_url' => env('ANTON_CULTURAL_API_URL'),
                'token' => env('ANTON_CULTURAL_TOKEN'),
                'enabled' => env('ANTON_CULTURAL_ENABLED', false),
            ],
        ],
    ],
];
```

## 🌍 Multi-Language Wikipedia

Search across multiple Wikipedia language versions simultaneously:

### Supported Languages
- **European**: de, en, fr, it, es, pt, nl, sv, da, no, fi, pl
- **Other**: ru, ja, zh, ko, ar, he, hi

### Usage Examples

```php
$provider = ProviderFactory::create('multilanguage-wikipedia');

// Search in specific languages
$results = $provider->search('Einstein', [
    'languages' => ['de', 'en', 'fr'],
    'limit' => 5
]);

// Search across all supported languages
$allResults = $provider->searchAllLanguages('Zurich', 2);

// Get article in specific language
$article = $provider->getArticle('Albert Einstein', 'en');
```

Each result includes:
- `language`: Language code (e.g., 'de', 'en')
- `language_name`: Human-readable language name
- `url`: Direct link to the Wikipedia article

## 🏛️ Multi-Instance Anton

Configure and use multiple Anton instances with different APIs:

### Configuration

```php
'anton' => [
    'instances' => [
        'archival' => [
            'name' => 'Archival Anton',
            'api_url' => 'https://archives.anton.ch',
            'token' => env('ANTON_ARCHIVAL_TOKEN'),
            'enabled' => true,
        ],
        'library' => [
            'name' => 'Library Anton',
            'api_url' => 'https://library.anton.ch',
            'token' => env('ANTON_LIBRARY_TOKEN'),
            'enabled' => true,
        ],
    ],
],
```

### Usage Examples

```php
$provider = ProviderFactory::create('multi-instance-anton');

// Search in specific instance
$provider->setInstance('archival');
$results = $provider->search('Document', ['size' => 10], 'objects');

// Search across all instances
$allResults = $provider->searchAllInstances('Archive');

// Search in specific instance without changing current
$specificResults = $provider->searchInInstance('library', 'Manuscript');
```

## 🛡️ Error Handling

The package includes comprehensive error handling to prevent page crashes:

### Features
- **Automatic Error Catching**: All HTTP exceptions are caught and handled
- **User-Friendly Messages**: Clear error messages instead of technical details
- **Graceful Degradation**: Empty results instead of application crashes
- **Detailed Logging**: Technical details logged for debugging

### Error Types Handled
- Network timeouts and connection failures
- HTTP 4xx errors (Bad Request, Not Found, Unauthorized)
- HTTP 5xx errors (Server Error, Service Unavailable)
- Invalid parameters and malformed responses

### Template Usage

```blade
<livewire:gnd-lw-component :model="$model" search="Einstein" />

@if($hasError)
    <div class="alert alert-warning">
        <strong>Search Error:</strong> {{ $errorMessage }}
        <p class="text-sm text-gray-600 mt-1">
            Please try a different search term or contact support if the problem persists.
        </p>
    </div>
@endif
```

## 🔧 Creating Custom Providers

### 1. Generate Provider

```bash
php artisan make:resources-provider MyNewProvider
```

### 2. Implement Provider

```php
<?php

namespace App\Providers;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

class MyNewProvider extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return 'https://api.example.com/';
    }

    public function getProviderName(): string
    {
        return 'MyNewProvider';
    }

    public function search(string $search, array $params = []): array
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);
        
        $result = $this->makeRequest('GET', '/search?q=' . urlencode($search));
        
        return $result->data ?? [];
    }
}
```

### 3. Create Livewire Component

```php
<?php

namespace App\Livewire;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class MyNewProviderLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'mynewprovider';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('mynewprovider');
    }

    protected function processResults($results): array
    {
        return $results;
    }
}
```

### 4. Register Provider

```php
// In a Service Provider
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

ProviderFactory::register('mynewprovider', MyNewProvider::class);
```

## 🧪 Testing

### Run All Tests

```bash
vendor/bin/phpunit
```

### Run Specific Tests

```bash
vendor/bin/phpunit tests/Unit/GndTest.php
vendor/bin/phpunit tests/Unit/MultiLanguageWikipediaTest.php
vendor/bin/phpunit tests/Unit/MultiInstanceAntonTest.php
```

### Test Coverage

```bash
vendor/bin/phpunit --coverage-html coverage/
```

## 📚 Available Providers

### Standard Providers

| Provider | Description | Configuration |
|----------|-------------|---------------|
| **GND** | Gemeinsame Normdatei (persons, organizations, places) | No API key required |
| **Wikidata** | Structured data from Wikipedia | No API key required |
| **Wikipedia** | Wikipedia articles | No API key required |
| **Geonames** | Geographic database | Username required |
| **Metagrid** | Metadata aggregator for Swiss archives | No API key required |
| **Idiotikon** | Swiss German dictionary | No API key required |
| **Ortsnamen** | Swiss place names database | No API key required |
| **Anton** | Customizable provider for specific APIs | API URL and token required |

### Advanced Providers

| Provider | Description | Features |
|----------|-------------|----------|
| **MultiLanguage Wikipedia** | Multi-language Wikipedia search | 19 languages, simultaneous search |
| **Multi-Instance Anton** | Multiple Anton configurations | Multiple APIs, instance management |

## 🚧 Requirements

- PHP ^8.1
- Laravel ^9.0\|^10.0\|^11.0
- Livewire ^3.4
- GuzzleHTTP ^7.0

## 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For questions or issues, please open an issue on GitHub or contact the maintainers.

---

**Version 2.1.0** - Built with ❤️ for the Laravel community
