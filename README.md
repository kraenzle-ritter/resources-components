# Resources Components for Laravel

[![Latest Stable Version](https://poser.pugx.org/kraenzle-ritter/resources-components/v)](//packagist.org/packages/kraenzle-ritter/resources-components) 
[![Total Downloads](https://poser.pugx.org/kraenzle-ritter/resources-components/downloads)](//packagist.org/packages/kraenzle-ritter/resources-components) 
[![License](https://poser.pugx.org/kraenzle-ritter/resources-components/license)](//packagist.org/packages/kraenzle-ritter/resources-components) 
[![Tests](https://github.com/kraenzle-ritter/resources-components/actions/workflows/php-tests.yml/badge.svg)](https://github.com/kraenzle-ritter/resources-components/actions/workflows/php-tests.yml) 
[![codecov](https://codecov.io/gh/kraenzle-ritter/resources-components/graph/badge.svg?token=13BQJVIHOV)](https://codecov.io/gh/kraenzle-ritter/resources-components)

Search for entities in authority databases and link them with your local data using Livewire components. This package provides a seamless integration with various data providers to enhance your Laravel application with external resources.

## Supported Providers

- [GND](https://lobid.org/gnd) (Gemeinsame Normdatei)
- [Geonames](http://www.geonames.org/) (Geographical database)
- [Wikipedia](https://www.wikipedia.org/) (Multiple languages: DE, EN, FR, IT)
- [Wikidata](https://www.wikidata.org/) (Structured data)
- [Idiotikon](https://www.idiotikon.ch/) (Swiss German dictionary)
- [Ortsnamen.ch](https://ortsnamen.ch/) (Swiss place names)
- [Metagrid](https://metagrid.ch/) (Swiss humanities database network)
- [Anton API](https://anton.ch/) (Archives and collections)
  - [Archiv der Georg Fischer AG](https://archives.georgfischer.com)
  - [Gosteli Archiv](https://gosteli.anton.ch)
  - [Karl Barth-Archiv](https://kba.karl-barth.ch)
- Manual Input (Custom links)

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Livewire 3.4+
- [kraenzle-ritter/resources](https://github.com/kraenzle-ritter/resources) package

## Installation

Via Composer:

```bash
composer require kraenzle-ritter/resources-components
```

After installation, you can publish various assets:

### Configuration
Publish the configuration file to customize provider settings:

```bash
php artisan vendor:publish --tag=resources-components.config
```

This will create `config/resources-components.php` where you can:
- Configure API endpoints and credentials
- Add custom providers
- Modify provider labels and URLs
- Set environment-specific settings (API tokens, etc.)

### Language Files
Publish translation files for customization:

```bash
php artisan vendor:publish --tag=resources-components.lang
```

### All Assets
Publish everything at once:

```bash
php artisan vendor:publish --provider="KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider"
```

## Testing

This package includes comprehensive tests to ensure proper functionality of all providers. Run the tests with:

```bash
vendor/bin/phpunit
```

## Basic Usage

In your views, use the components like this:

```blade
@livewire('resources-list', [$model, 'deleteButton' => true])
@livewire('provider-select', [$model, $providers, 'actors'])
```

Where:

- `$model` is the model that should become resourceable (must use the `HasResources` trait)
- `$providers` is an array of provider keys to enable (e.g., `['gnd', 'geonames', 'wikipedia-de', 'manual-input']`)
- The third parameter (`'actors'`) is the endpoint entity type (only required for Anton API providers)

## Configuration

The package comes with a pre-configured setup for various providers. After publishing the configuration file (see Installation), you can customize provider settings in `config/resources-components.php`:

```php
// config/resources-components.php
return [
    'limit' => 5, // Default search results limit
    'providers' => [
        'gnd' => [
            'label' => 'GND',
            'api-type' => 'Gnd',
            'base_url' => 'https://lobid.org/gnd/',
            'target_url' => 'https://d-nb.info/gnd/{provider_id}',
            'test_search' => 'Hannah Arendt', // Test query for provider check page
        ],
        'wikipedia-de' => [
            'label' => 'Wikipedia (DE)',
            'api-type' => 'Wikipedia',
            'base_url' => 'https://de.wikipedia.org/w/api.php',
            'target_url' => 'https://de.wikipedia.org/wiki/{underscored_name}',
            'test_search' => 'Bertha von Suttner',
        ],
        // Add more providers here
    ],
];
```

### Provider Configuration Options

Each provider supports the following configuration options:

- **`label`** (string): Display name for the provider
- **`api-type`** (string): Provider class name (e.g., 'Gnd', 'Wikipedia', 'Wikidata')
- **`base_url`** (string): Base URL for API requests
- **`target_url`** (string): URL template for saved resources (supports placeholders like `{provider_id}`)
- **`test_search`** (string): Test query used by the provider check page to verify functionality
- **`limit`** (integer, optional): Provider-specific result limit (overrides global limit)

Additional provider-specific options may apply (e.g., `user_name` for Geonames, `api_token` for Anton providers).

## Creating Custom Providers

You can create your own provider by implementing the `ProviderInterface` or extending the `AbstractProvider` class:

1. Create a provider class:

```php
namespace App\Providers;

use KraenzleRitter\ResourcesComponents\Providers\AbstractProvider;

class MyCustomProvider extends AbstractProvider
{
    public function search(string $search, array $params = [])
    {
        // Implement search logic
    }
    
    public function processResult($results): array
    {
        // Process results into standard format
        return [
            [
                'title' => 'Result title',
                'description' => 'Result description',
                'url' => 'https://example.com/resource',
                'raw_data' => json_encode($data)
            ],
            // More results...
        ];
    }
}
```

2. Register your provider in the configuration:

```php
'my-provider' => [
    'label' => 'My Provider',
    'api-type' => 'MyCustom',
    'base_url' => 'https://api.example.com/',
    'provider_class' => App\Providers\MyCustomProvider::class,
],
```

## Customizing Views

You can publish and customize the views:

```bash
php artisan vendor:publish --provider="KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider" --tag=resources-components.views
```

### Handling Resource Events

The components fire an event (`ResourceSaved`) when saving a resource. You can define and register a listener in your app:

```php
<?php

namespace App\Listeners;

use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class UpdateLocationWithGeonamesCoordinates
{
    public function handle(ResourceSaved $event)
    {
        if ($event->resource->provider == 'geonames') {
            // Access resource data
            \Log::debug($event->resource);
            
            // Access the model that the resource is attached to
            \Log::debug($event->model);
            
            // Example: Update location coordinates from Geonames data
            if (isset($event->resource->data['lat']) && isset($event->resource->data['lng'])) {
                $event->model->update([
                    'latitude' => $event->resource->data['lat'],
                    'longitude' => $event->resource->data['lng']
                ]);
            }
        }
    }
}
```

Register your listener in `EventServiceProvider.php`:

```php
<?php
namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use App\Listeners\UpdateLocationWithGeonamesCoordinates;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ResourceSaved::class => [
            UpdateLocationWithGeonamesCoordinates::class
        ]
    ];
}
```

## Configuration

### Environment Variables

Some providers require additional configuration in your `.env` file:

```
# For Geonames
GEONAMES_USERNAME=your_username

# Anton API Providers (georgfischer, kba, gosteli)
# No API tokens required for these providers
```

## Model Requirements

Your models must:

1. Use the `HasResources` trait from the `kraenzle-ritter/resources` package
2. Have either a `resource_search` attribute or a `name` attribute (used as default search term)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use KraenzleRitter\Resources\HasResources;

class Person extends Model
{
    use HasResources;
    
    // The rest of your model...
}
```

## Creating Custom Providers

To create a new provider, you need:

1. A provider class that implements the search functionality
2. A Livewire component class for the UI interaction
3. Configuration in the `resources-components.php` file

See the documentation or existing providers for implementation details.

## License

MIT. Please see the [license file](LICENSE.md) for more information.
