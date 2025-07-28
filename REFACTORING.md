# Refactoring Documentation

## Overview of Improvements

This documentation describes the refactoring measures carried out for the `resources-components` package.

### Completed

#### 1. Abstract Base Classes
- AbstractProvider: Common functionality for all providers
  - HTTP request handling with error management
  - Configuration management
  - Search string sanitization
  - Cache integration
- AbstractLivewireComponent: Base for all Livewire components
  - Standardized mount(), saveResource(), removeResource() methods
  - Unified view logic
  - Event handling

#### 2. Interface Definition
- ProviderInterface: Defines unified API for all providers
  - `search(string $search, array $params = [])`
  - `getProviderName(): string`
  - `getBaseUrl(): string`

#### 3. Factory Pattern
- ProviderFactory: Central provider creation
  - `create(string $provider): ProviderInterface`
  - `register(string $name, string $class): void`
  - `getAvailableProviders(): array`
  - Easy extension with new providers

#### 4. Cache System
- CacheService: Automatic caching of API requests
  - Configurable TTL and cache prefixes
  - Provider-specific cache management
  - Cache invalidation

#### 5. Test Infrastructure
- TestCase: Base for all tests
- Unit tests for providers with mock support
- Factory tests for provider registration
- PHPUnit configuration

#### 6. Artisan Commands
- MakeProviderCommand: `php artisan make:resources-provider ProviderName`
  - Automatic generation of provider classes
  - Livewire component creation
  - Standardized code templates

#### 7. Refactored Providers
- Gnd: Completely refactored with new architecture
- Wikidata: Moved to AbstractProvider
- Wikipedia: With cache support and better error handling
- Geonames: Refactored with improved parameter handling
- Metagrid: Refactored with standardized methods
- Idiotikon: Simplified and standardized
- Ortsnamen: Refactored with better error handling
- Anton: Refactored with improved token handling

### Completed Features

#### 8. Views Standardization
- Unified Blade templates
- Responsive design
- Accessibility improvements

#### 9. Performance Optimizations
- Rate limiting implementation
- Connection pooling
- Automatic caching system

#### 10. Extended Tests
- Integration tests
- Feature tests for Livewire components
- Complete unit test coverage

## Code Examples

### Creating New Provider

```bash
php artisan make:resources-provider MyNewProvider
```

### Using Provider

```php
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

// Create provider
$provider = ProviderFactory::create('gnd');

// Search with cache
$results = $provider->searchWithCache('Einstein', ['limit' => 10]);

// Clear cache
$provider->clearCache();
```

### Registering Custom Provider

```php
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

// In a Service Provider
ProviderFactory::register('mycustomprovider', MyCustomProvider::class);
```

## Migration Guide

### For Existing Providers

1. Extend Provider Class:
```php
// Before
class MyProvider
{
    public function search($search, $params) { ... }
}

// After  
class MyProvider extends AbstractProvider
{
    public function getBaseUrl(): string { return 'https://api.example.com/'; }
    public function getProviderName(): string { return 'MyProvider'; }
    public function search(string $search, array $params = []) { ... }
}
```

2. Adapt Livewire Component:
```php
// Before
class MyProviderLwComponent extends Component
{
    // Much duplicated code...
}

// After
class MyProviderLwComponent extends AbstractLivewireComponent
{
    protected function getProviderName(): string { return 'MyProvider'; }
    protected function getProviderClient() { return ProviderFactory::create('myprovider'); }
    protected function processResults($results) { return $results; }
}
```

## Testing

```bash
# Run all tests
vendor/bin/phpunit

# Specific tests
vendor/bin/phpunit tests/Unit/GndTest.php
vendor/bin/phpunit tests/Unit/ProviderFactoryTest.php

# With coverage
vendor/bin/phpunit --coverage-html coverage/
```

## Configuration

```php
// config/resources-components.php
return [
    'providers' => [
        'myprovider' => [
            'enabled' => true,
            'limit' => 5,
            'api_key' => env('MYPROVIDER_API_KEY'),
            'timeout' => 30,
        ],
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
```

## Breaking Changes

### v2.0.0
- All providers now require the `ProviderInterface`
- Livewire components expect standardized method signatures
- Configuration moved from individual provider configs to central config

### Migration
- Update provider classes to new base classes
- Update configuration files
- Test Livewire components on new API

## Benefits

### For Developers
- Less code duplication
- Unified APIs
- Easy extension
- Better testability
- Automatic caching

### For Performance  
- HTTP request optimization
- Intelligent caching
- Reduced API calls
- Better error handling

### For Maintenance
- Central configuration
- Standardized logs
- Consistent error handling
- Easy updates
