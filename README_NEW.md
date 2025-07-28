# Resources Components Package

Ein Laravel-Package für die Integration verschiedener externer Datenquellen zur Suche nach Personen, Organisationen und Orten.

## Features

- **Multiple Provider Support**: GND, Wikidata, Wikipedia, Geonames, Metagrid, Idiotikon, Ortsnamen, Anton
- **Livewire Components**: Einfache Integration in Laravel-Anwendungen
- **Caching**: Automatisches Caching von API-Anfragen für bessere Performance
- **Erweiterbar**: Neue Provider können einfach hinzugefügt werden
- **Tests**: Umfassende Testsuite
- **Factory Pattern**: Vereinfachte Provider-Erstellung und -Verwaltung

## Installation

```bash
composer require kraenzle-ritter/resources-components
```

## Konfiguration

Veröffentlichen Sie die Konfigurationsdatei:

```bash
php artisan vendor:publish --tag="resources-components.config"
```

Konfigurieren Sie die gewünschten Provider in `config/resources-components.php`:

```php
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
        // weitere Provider...
    ],
];
```

## Verwendung

### Livewire Components

```php
// In Ihrer Blade-Vorlage
<livewire:gnd-lw-component :model="$yourModel" search="Einstein" />
<livewire:wikidata-lw-component :model="$yourModel" search="Zürich" />
```

### Direkte Provider-Nutzung

```php
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

// Provider erstellen
$gndProvider = ProviderFactory::create('gnd');

// Suche durchführen
$results = $gndProvider->search('Einstein', ['limit' => 10]);
```

### Cache Service

```php
use KraenzleRitter\ResourcesComponents\Services\CacheService;

$cacheService = new CacheService();

// Suche mit Caching
$results = $cacheService->remember($provider, 'Einstein', ['limit' => 5]);

// Cache leeren
$cacheService->flush();
```

## Neue Provider erstellen

### Mit Artisan Command

```bash
php artisan make:resources-provider MyNewProvider
```

Dies erstellt automatisch:
- Provider-Klasse (`MyNewProvider.php`)
- Livewire-Component (`MyNewProviderLwComponent.php`)

### Manuell

1. **Provider-Klasse erstellen**:

```php
<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

class MyProvider extends AbstractProvider
{
    public function getBaseUrl(): string
    {
        return 'https://api.myprovider.com/';
    }

    public function getProviderName(): string
    {
        return 'MyProvider';
    }

    public function search(string $search, array $params = [])
    {
        $search = $this->sanitizeSearch($search);
        $params = $this->mergeParams($params);
        
        $searchQuery = "search?q=" . urlencode($search);
        $result = $this->makeRequest('GET', $searchQuery);
        
        return $result;
    }
}
```

2. **Livewire Component erstellen**:

```php
<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class MyProviderLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string
    {
        return 'MyProvider';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('myprovider');
    }

    protected function processResults($results)
    {
        // Ergebnisse verarbeiten und standardisieren
        return $results;
    }

    public function render()
    {
        $results = [];
        
        if ($this->search) {
            $client = $this->getProviderClient();
            $resources = $client->search($this->search, $this->queryOptions);
            $results = $this->processResults($resources);
        }

        return view($this->getViewName(), [
            'results' => $results
        ]);
    }
}
```

3. **Provider registrieren**:

```php
// In einem Service Provider
ProviderFactory::register('myprovider', MyProvider::class);

// Livewire Component registrieren
Livewire::component('my-provider-lw-component', MyProviderLwComponent::class);
```

## Tests ausführen

```bash
vendor/bin/phpunit
```

## Verfügbare Provider

- **GND**: Gemeinsame Normdatei (Personen, Organisationen, Orte)
- **Wikidata**: Strukturierte Daten aus Wikipedia
- **Wikipedia**: Wikipedia-Artikel
- **Geonames**: Geografische Datenbank
- **Metagrid**: Metadaten-Aggregator
- **Idiotikon**: Schweizerdeutsches Wörterbuch
- **Ortsnamen**: Schweizer Ortsnamen
- **Anton**: Angepasster Provider

## Architektur

### Abstrakte Klassen

- `AbstractProvider`: Basis für alle Provider mit gemeinsamer Funktionalität
- `AbstractLivewireComponent`: Basis für alle Livewire-Components

### Interfaces

- `ProviderInterface`: Definiert die Provider-API

### Services

- `CacheService`: Verwaltet Caching von API-Anfragen
- `ProviderFactory`: Factory Pattern für Provider-Erstellung

## Beitragen

1. Fork des Repositories
2. Feature-Branch erstellen
3. Tests schreiben
4. Code implementieren
5. Pull Request erstellen

## Lizenz

MIT License

## Changelog

Siehe [CHANGELOG.md](CHANGELOG.md) für Details zu Änderungen.
