# Resources components (Livewire, Laravel)

[![Latest Stable Version](https://poser.pugx.org/kraenzle-ritter/resources-components/v)](//packagist.org/packages/kraenzle-ritter/resources-components) [![Total Downloads](https://poser.pugx.org/kraenzle-ritter/resources-components/downloads)](//packagist.org/packages/kraenzle-ritter/resources-components) [![Latest Unstable Version](https://poser.pugx.org/kraenzle-ritter/resources-components/v/unstable)](//packagist.org/packages/kraenzle-ritter/resources-components) [![License](https://poser.pugx.org/kraenzle-ritter/resources-components/license)](//packagist.org/packages/kraenzle-ritter/resources-components)

Search for entities in authority databases and link them with your local data.

- Anton
- Geonames
- GND
- Metagrid
- ortsnamen.ch
- Wikidata
- Wikipedia

## Installation

Via Composer

``` bash
$ composer require kraenzle-ritter/resources-component
```

To use this package install also `kraenzle-ritter/resources`.

In your views you can use the package then like this:

```
@livewire('resources-list', [$model, 'deleteButton' => true])
@livewire('provider-select', [$model, $providers', 'actors'])
```

The `$model` is the model which should become resourceable.

`$providers` is a list (array) of actually used resource providers. You can pass them by the controller or via a config file. Actual available are any anton installation (see below), geonames, gnd, metagrid, wikipedia, wikidata.

The last parameter for the `provider-select` is the endpoint, that is the entity. You only need this at this time if you use anton as a provider.

The components fire an event (`ResourceSaved`) when saving a resource. So you can define and register a listener in your app:

```php
<?php

namespace App\Listeners;

use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class UpdateLocationWithGeonamesCoordinates
{

    public function handle(ResourceSaved $event)
    {
        if ($this->resource->provider == 'geonames') {
            \Log::debug($event->resource);
            \Log::debug($event->model);
        }

    }
}
```

In your EventServiceProvider:

```php
<?php
namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use App\Listeners\UpdateLocationWithGeonamesCoordinates;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ResourceSaved::class => [
            UpdateLocationWithGeonamesCoordinates::class
        ]
    ];
```

## .env Variables

For some providers you need set some variables in your .env file:

```
ANTON_PROVIDER_SLUG=kr
ANTON_URL=https://kr.anton.ch
ANTON_API_URL=http://kkr.anton.test/api
ANTON_API_TOKEN=secret

GEONAMES_USERNAME=demo
```

## Models

The model should have a resources_search attribute or a name attribute.

## Usage
Just put the component in your view:

```
@livewire('gnd-lw-component', ['params' => ['queryOptions' => ['size' => 5]]])
```

## License

MIT. Please see the [license file](LICENSE.md) for more information.
