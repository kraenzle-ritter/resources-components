# Upgrading from v1.x to v2.x

This guide helps you upgrade your application from resources-components v1.x to v2.x.

## Breaking Changes

### 1. Component Parameter Structure

The way parameters are passed to Livewire components has changed:

#### v1.x:
```php
// Most components received parameters via a 'params' array
$componentParams = [
    'model' => $model,
    'search' => $search,
    'params' => ['providerKey' => $providerKey]
];

// Wikipedia components were a special case
$componentParams = [
    'model' => $model,
    'search' => $search,
    'providerKey' => $providerKey
];
```

#### v2.x:
All components now use the same consistent pattern:
```php
$componentParams = [
    'model' => $model,
    'search' => $search,
    'providerKey' => $providerKey
];
```

### 2. Component Namespace

Components have moved to a dedicated namespace:

#### v1.x:
```php
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
```

#### v2.x:
```php
use KraenzleRitter\ResourcesComponents\Components\WikipediaLivewireComponent;
```

### 3. Provider Implementation

Providers have been moved to a dedicated namespace and structure:

#### v1.x:
```php
use KraenzleRitter\ResourcesComponents\Wikipedia;
```

#### v2.x:
```php
use KraenzleRitter\ResourcesComponents\Providers\WikipediaProvider;
```

## Migration Steps

### Step 1: Update Composer Dependencies

```bash
composer require kraenzle-ritter/resources-components:^2.0
```

### Step 2: Publish Config File (Optional)

```bash
php artisan vendor:publish --tag=resources-components.config
```

### Step 3: Update Custom Component References

If you have custom views referencing the Livewire components, update the references:

```blade
{{-- v1.x --}}
<livewire:wikipedia-lw-component :model="$model" :providerKey="'wikipedia-en'" />

{{-- v2.x --}}
<livewire:wikipedia-lw-component-v2 :model="$model" :providerKey="'wikipedia-en'" />
```

During the transition period, both v1 and v2 components are available:
- v1: `wikipedia-lw-component`
- v2: `wikipedia-lw-component-v2`

### Step 4: Update Custom Provider Implementations

If you've created custom providers, update them to follow the new structure:

1. Create a class that extends `KraenzleRitter\ResourcesComponents\Providers\AbstractProvider`
2. Implement the `ProviderInterface` methods
3. Create a Livewire component in the Components namespace

### Step 5: Register Custom Providers

Register your custom providers in a service provider:

```php
public function boot()
{
    // Register provider with the factory
    app('resources-components.provider-factory')->extend(
        'my-custom-provider', 
        MyCustomProvider::class
    );
    
    // Register Livewire component
    Livewire::component(
        'my-custom-lw-component', 
        MyCustomLivewireComponent::class
    );
}
```

## Using the Provider Factory

The v2.x release includes a provider factory for easier provider management:

```php
// Get provider factory
$factory = app('resources-components.provider-factory');

// Create provider instance
$provider = $factory->make('wikipedia-en');

// Search for resources
$results = $provider->search('Albert Einstein', ['limit' => 5]);
$processedResults = $provider->processResult($results);

// Get available providers
$availableProviders = $factory->getAvailableProviders();
```

## Temporary Backward Compatibility

During the transition period, both v1 and v2 components are available. This allows you to migrate your application gradually. In a future release (v3.0), the v1 components will be removed.
