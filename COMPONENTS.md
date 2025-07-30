# Components Structure

This document outlines the recommended structure for implementing new provider components.

## Providers Directory

The `Providers` directory contains concrete implementations of data providers. Each provider should:

1. Extend `AbstractProvider`
2. Implement `search()` and `processResult()` methods
3. Handle API calls to the external service

## Components Directory

The `Components` directory contains Livewire components that provide the UI for interacting with providers. Each component should:

1. Use `ProviderComponentTrait`
2. Handle UI state (search, results, errors)
3. Render the appropriate view template

## Traits Directory

Contains traits used across multiple components:

- `ProviderComponentTrait`: Common functionality for provider components

## Example Implementation

### Provider Class

```php
// src/Providers/MyNewProvider.php
namespace KraenzleRitter\ResourcesComponents\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MyNewProvider extends AbstractProvider
{
    protected $client;
    
    public function __construct(string $providerKey, array $config = [])
    {
        parent::__construct($providerKey, $config);
        
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }
    
    public function search(string $search, array $params = [])
    {
        $limit = $params['limit'] ?? 5;
        
        try {
            $response = $this->client->request('GET', 'search', [
                'query' => [
                    'q' => $search,
                    'limit' => $limit
                ]
            ]);
            
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    public function processResult($results): array
    {
        if (!$results) {
            return [];
        }
        
        $processed = [];
        foreach ($results->items as $item) {
            $processed[] = [
                'title' => $item->title,
                'description' => $item->description,
                'url' => $item->url,
                'raw_data' => json_encode($item)
            ];
        }
        
        return $processed;
    }
}
```

### Livewire Component

```php
// src/Components/MyNewLivewireComponent.php
namespace KraenzleRitter\ResourcesComponents\Components;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Providers\MyNewProvider;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;

class MyNewLivewireComponent extends Component
{
    use ProviderComponentTrait;
    
    public $search;
    public $model;
    public $providerKey = 'my-new-provider';
    public $providerLabel = 'My New Provider';
    public $results = [];
    public $error = null;
    
    protected $provider;
    protected $listeners = ['resourcesChanged' => 'render'];
    
    public function mount($model, string $search = '', string $providerKey = 'my-new-provider')
    {
        $this->model = $model;
        $this->search = $search;
        $this->providerKey = $providerKey;
        
        $config = config('resources-components.providers.' . $providerKey) ?? [
            'base_url' => 'https://api.example.com/',
            'label' => $this->providerLabel
        ];
        
        $this->provider = new MyNewProvider($providerKey, $config);
    }
    
    public function search()
    {
        if (empty($this->search)) {
            $this->results = [];
            return;
        }
        
        $params = [
            'limit' => config('resources-components.limit', 5)
        ];
        
        $rawResults = $this->provider->search($this->search, $params);
        $this->results = $this->provider->processResult($rawResults);
    }
    
    public function saveResource(string $title, string $url, ?string $rawData = null)
    {
        $resourceData = [
            'provider' => $this->providerKey,
            'title' => $title,
            'url' => $url
        ];
        
        if ($rawData) {
            $resourceData['data'] = $rawData;
        }
        
        $resource = $this->saveResourceToModel($this->model, $resourceData);
        $this->search = '';
        $this->results = [];
        
        event(new ResourceSaved($resource, $this->model->id));
    }
    
    public function render()
    {
        return view('resources-components::livewire.my-new-provider', [
            'model' => $this->model,
            'results' => $this->results
        ]);
    }
}
```

### Service Provider Registration

Add your new component to `ResourcesComponentsServiceProvider`:

```php
// In the boot method
Livewire::component('my-new-lw-component', MyNewLivewireComponent::class);

// In the provides method
public function provides()
{
    return [
        // ...other components
        'my-new-lw-component',
    ];
}
```

### Configuration

Add your provider to the `resources-components.php` config:

```php
'my-new-provider' => [
    'label' => 'My New Provider',
    'api-type' => 'MyNew',
    'base_url' => 'https://api.example.com/',
    'api_key' => env('MY_NEW_PROVIDER_API_KEY', ''),
],
```

### View Template

Create a view template at `resources/views/livewire/my-new-provider.blade.php`:

```blade
<div>
    <div class="form-group">
        <input type="text" class="form-control" placeholder="Search..." wire:model.defer="search">
        <button class="btn btn-primary mt-2" wire:click="search">Search</button>
    </div>
    
    @if($error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endif
    
    @if(count($results))
        <ul class="list-group mt-3">
            @foreach($results as $result)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>{{ $result['title'] }}</h5>
                            <p>{{ $result['description'] }}</p>
                            <a href="{{ $result['url'] }}" target="_blank">{{ $result['url'] }}</a>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-success" wire:click="saveResource('{{ $result['title'] }}', '{{ $result['url'] }}', '{{ $result['raw_data'] }}')">Save</button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @elseif($search)
        <div class="alert alert-info mt-3">No results found</div>
    @endif
</div>
```
