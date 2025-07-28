# Resources Components Package

Ein Laravel-Package für die Integration verschiedener externer Datenquellen zur Suche nach Personen, Organisationen und Orten.

## Features

- **Multiple Provider Support**: 
  - GND: https://lobid.org/gnd/
  - Wikidata: https://www.wikidata.org/
  - Wikipedia: https://{$locale}.wikipedia.org/w/api.php
  - Geonames: http://api.geonames.org/
  - Metagrid: 
  - Idiotikon: 
  - Ortsnamen: 
  - Anton: *.anton.ch
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
use KraenzleRitter\ResourcesComponents\Services\CacheService;

// Provider erstellen
$gndProvider = ProviderFactory::create('gnd');

// Suche durchführen
$results = $gndProvider->search('Einstein', ['limit' => 10]);
```

### Cache Service

```php
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

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

- **GND**: Gemeinsame Normdatei (Personen, Organisationen, Orte) - https://lobid.org/gnd/
- **Wikidata**: Strukturierte Daten aus Wikipedia - https://www.wikidata.org/
- **Wikipedia**: Wikipedia-Artikel - https://{locale}.wikipedia.org/w/api.php
- **Geonames**: Geografische Datenbank - http://api.geonames.org/
- **Metagrid**: Metadaten-Aggregator - 
- **Idiotikon**: Schweizerdeutsches Wörterbuch
- **Ortsnamen**: Schweizer Ortsnamen-Datenbank
- **Anton**: Angepasster Provider für spezifische APIs

## Architektur

### Abstrakte Klassen

- `AbstractProvider`: Basis für alle Provider mit gemeinsamer Funktionalität
  - HTTP-Client-Management
  - Automatisches Caching
  - Fehlerbehandlung
  - Parameter-Normalisierung
  - Suchstring-Sanitization

- `AbstractLivewireComponent`: Basis für alle Livewire-Components
  - Standardisierte mount/save/remove Methoden
  - Factory Pattern Integration
  - View-Namen Konventionen

### Interfaces

- `ProviderInterface`: Definiert die Provider-API
  - `search(string $search, array $params = [])`
  - `getProviderName(): string`
  - `getBaseUrl(): string`

### Services

- `CacheService`: Verwaltet Caching von API-Anfragen
  - Konfigurierbare TTL pro Provider
  - Automatische Cache-Key-Generierung
  - Provider-spezifische Cache-Verwaltung

- `ProviderFactory`: Factory Pattern für Provider-Erstellung
  - Dynamische Provider-Registrierung
  - Verfügbarkeits-Checks
  - Singleton-Pattern für Performance

### Console Commands

- `MakeProviderCommand`: Artisan-Command zum Erstellen neuer Provider
  ```bash
  php artisan make:resources-provider MyNewProvider
  ```

## Performance & Caching

Das Package implementiert ein automatisches Caching-System:

```php
// Automatisches Caching - wird transparent von AbstractProvider verwendet
$result = $provider->search('Einstein'); // Erste Anfrage → API Call
$result = $provider->search('Einstein'); // Zweite Anfrage → Cache Hit

// Cache manuell verwalten
$cacheService = new CacheService();
$cacheService->clearProviderCache('gnd');
$cacheService->flush(); // Alle Caches leeren
```

## Error Handling

Alle Provider implementieren einheitliche Fehlerbehandlung:

- HTTP-Fehler werden automatisch geloggt
- Netzwerk-Timeouts führen zu leeren Arrays
- Malformed JSON wird abgefangen
- API-Rate-Limits werden respektiert

## Konfiguration pro Provider

```php
// config/resources-components.php
return [
    'providers' => [
        'gnd' => [
            'enabled' => true,
            'limit' => 5,
            'timeout' => 30,
            'cache_ttl' => 3600, // 1 Stunde
            'base_url' => 'https://lobid.org/gnd/',
        ],
        'wikidata' => [
            'enabled' => true,
            'locale' => 'de',
            'limit' => 10,
            'cache_ttl' => 7200, // 2 Stunden
        ],
        'geonames' => [
            'enabled' => true,
            'username' => env('GEONAMES_USERNAME'),
            'limit' => 5,
            'cache_ttl' => 86400, // 24 Stunden
        ],
    ],
    'cache' => [
        'default_ttl' => 3600,
        'enabled' => true,
    ],
];
```

## Migration von v1 zu v2

Falls Sie von einer älteren Version migrieren:

1. **Provider-Instanziierung**:
   ```php
   // Alt
   $gnd = new Gnd();
   
   // Neu (empfohlen)
   $gnd = ProviderFactory::create('gnd');
   ```

2. **Livewire Components**:
   ```php
   // Alt - direkte Provider-Instanziierung
   $this->provider = new Gnd();
   
   // Neu - Factory Pattern
   $this->provider = ProviderFactory::create('gnd');
   ```

3. **Caching**:
   - Automatisch aktiviert, keine manuellen Änderungen nötig
   - Alte Cache-Keys werden automatisch migriert

## Troubleshooting

### Häufige Probleme

1. **Provider nicht gefunden**:
   ```bash
   composer dump-autoload
   ```

2. **Tests schlagen fehl**:
   ```bash
   vendor/bin/phpunit --bootstrap vendor/autoload.php
   ```

3. **Cache-Probleme**:
   ```php
   $cacheService = new CacheService();
   $cacheService->flush();
   ```

4. **API-Timeouts**:
   ```php
   // In config/resources-components.php
   'providers' => [
       'gnd' => ['timeout' => 60] // Erhöhe Timeout
   ]
   ```

## Beitragen

1. Fork des Repositories
2. Feature-Branch erstellen: `git checkout -b feature/my-new-feature`
3. Tests schreiben
4. Code implementieren
5. Tests ausführen: `vendor/bin/phpunit`
6. Pull Request erstellen

### Coding Standards

- PSR-12 Coding Standard
- PHPUnit für Tests
- PHP 8+ Attributes statt Annotations

### Neue Provider hinzufügen

1. Provider-Klasse erstellen (extends AbstractProvider)
2. Livewire-Component erstellen (extends AbstractLivewireComponent)
3. Tests schreiben
4. In ProviderFactory registrieren
5. Dokumentation ergänzen

## Lizenz

MIT License

## Changelog

### v2.0.0 (2025-07-28)
- **BREAKING**: Vollständige Architektur-Refactoring
- Einführung von AbstractProvider und AbstractLivewireComponent
- Factory Pattern für Provider-Management
- Automatisches Caching-System
- Moderne PHP 8 Attributes in Tests
- Umfassende Test-Suite
- Artisan-Command für Provider-Generierung

### v1.x
- Legacy-Version mit individuellen Provider-Klassen

Siehe [CHANGELOG.md](CHANGELOG.md) für detaillierte Änderungen.
