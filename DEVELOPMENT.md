# Developer Documentation

This guide explains how to extend the `resources-components` package by creating new providers.

## Creating a New Provider

To add a new provider to the resources-components package, you need to create the following components:

1. Provider class (e.g., `MyNewProvider.php`)
2. Livewire component class (e.g., `MyNewProviderLwComponent.php`)
3. Livewire component view (e.g., `my-new-provider-lw-component.blade.php`)
4. Configuration entry in `resources-components.php`

### 1. Create the Provider Class

First, create a new provider class that handles the API communication or data retrieval logic.

```php
<?php

namespace KraenzleRitter\ResourcesComponents;

class MyNewProvider
{
    /**
     * Search for resources using the provider's API
     *
     * @param string $search Search term
     * @param array $params Additional parameters
     * @return array|object Search results
     */
    public function search(string $search, array $params = [])
    {
        // Set default options
        $options = array_merge([
            'limit' => config('resources-components.limit', 5),
            // Add other default parameters here
        ], $params);
        
        // Implement your API call or data retrieval logic
        $results = $this->fetchDataFromApi($search, $options);
        
        // Process results into a standardized format
        return $this->processResults($results);
    }
    
    /**
     * Process the API response into a standardized format
     *
     * @param array|object $results Raw API results
     * @return array Processed results in a standardized format
     */
    private function processResults($results)
    {
        $processed = [];
        
        // Transform the API-specific data structure into a standardized format
        // that can be used by the Livewire component
        
        // Example structure:
        // Each item should have at minimum:
        // - id: A unique identifier for the resource
        // - title: The main title or name
        // - snippet: A short description (optional)
        
        return $processed;
    }
    
    /**
     * Fetch data from the external API
     *
     * @param string $search Search term
     * @param array $options Search options
     * @return mixed Raw API response
     */
    private function fetchDataFromApi(string $search, array $options)
    {
        // Implement the actual API request here
        // This could use Guzzle, curl, or any HTTP client
        
        // Example:
        // $client = new \GuzzleHttp\Client();
        // $response = $client->get('https://api.example.com/search', [
        //     'query' => [
        //         'q' => $search,
        //         'limit' => $options['limit']
        //     ]
        // ]);
        // return json_decode($response->getBody());
    }
}
```

### 2. Create the Livewire Component Class

Next, create a Livewire component that will handle user interactions:

```php
<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;

class MyNewProviderLwComponent extends Component
{
    use ProviderComponentTrait;
    
    public $search;
    public $queryOptions;
    public $model;
    public $provider = 'MyNewProvider'; // Provider name (used for storage)
    public $base_url; // Base URL for creating links
    
    public $saveMethod = 'updateOrCreateResource'; // Method name for saving resources
    public $removeMethod = 'removeResource'; // Method name for removing resources
    
    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', string $providerKey = 'my-new-provider')
    {
        $this->model = $model;
        
        // Get configuration from the config file
        $apiUrl = config('resources-components.providers.' . $providerKey . '.base_url');
        $this->base_url = $apiUrl;
        
        // Set up search parameters
        $this->search = trim($search) ?: '';
        $this->queryOptions = [
            'providerKey' => $providerKey,
            'limit' => 5
        ];
    }
    
    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => $url
        ];
        
        // If you have additional data to store
        if ($full_json) {
            $data['data'] = $full_json;
        }
        
        // Save the resource using the HasResources trait method
        $resource = $this->model->{$this->saveMethod}($data);
        
        // Notify other components to update
        $this->dispatch('resourcesChanged');
        
        // Fire the ResourceSaved event
        event(new ResourceSaved($resource, $this->model->id));
    }
    
    public function render()
    {
        // Create an instance of your provider
        $client = new MyNewProvider();
        
        // Get results from the provider
        $resources = $client->search($this->search, $this->queryOptions);
        
        // Choose the appropriate view
        $view = view()->exists('vendor.kraenzle-ritter.livewire.my-new-provider-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.my-new-provider-lw-component'
              : 'resources-components::livewire.my-new-provider-lw-component';
        
        return view($view, [
            'results' => $resources,
            'base_url' => $this->base_url
        ]);
    }
}
```

### 3. Create the Blade View

Create a blade view in `resources/views/livewire/my-new-provider-lw-component.blade.php`:

```blade
@include('resources-components::livewire.partials.results-layout', [
    'providerKey' => 'my-new-provider',
    'providerName' => config('resources-components.providers.my-new-provider.label', 'My New Provider'),
    'model' => $model,
    'results' => $results,
    'saveAction' => function($result) use ($base_url) {
        return "saveResource('{$result->id}', '{$base_url}{$result->path}')";
    },
    'result_heading' => function($result) {
        return $result->title ?? '';
    },
    'result_content' => function($result) use ($base_url) {
        $output = "<a href=\"{$base_url}{$result->path}\" target=\"_blank\">{$base_url}{$result->path}</a>";
        
        if (!empty($result->snippet)) {
            $output .= "<br>" . $result->snippet;
        }
        
        return $output;
    }
])
```

### 4. Update the Configuration

Add your provider to the `config/resources-components.php` file:

```php
return [
    'providers' => [
        // Other providers...
        
        'my-new-provider' => [
            'label' => 'My New Provider',
            'api-type' => 'MyNewProvider', // This should match your class name
            'base_url' => 'https://api.example.com/',
            // Add any provider-specific configuration here
        ],
    ]
];
```

### 5. Register the Component

Update the `ResourcesComponentsServiceProvider.php` to register your new Livewire component:

```php
public function boot()
{
    // Other registrations...
    
    // Register your new component
    Livewire::component('my-new-provider-lw-component', MyNewProviderLwComponent::class);
}
```

## Testing Your New Provider

Create a test file in `tests/Feature/MyNewProviderTest.php`:

```php
<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\MyNewProvider;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use Livewire\Livewire;
use KraenzleRitter\ResourcesComponents\MyNewProviderLwComponent;

class MyNewProviderTest extends TestCase
{
    /** @test */
    public function it_can_search_and_return_results()
    {
        // Test the search method directly
        $provider = new MyNewProvider();
        $results = $provider->search('test query');
        
        $this->assertNotEmpty($results);
        // Add more assertions based on your expected results
    }
    
    /** @test */
    public function livewire_component_renders_correctly()
    {
        // Test the Livewire component
        $model = new DummyModel(['name' => 'Test Model']);
        
        Livewire::test(MyNewProviderLwComponent::class, [
            'model' => $model,
            'search' => 'test query'
        ])
        ->assertViewIs('resources-components::livewire.my-new-provider-lw-component')
        ->assertSee('My New Provider'); // The provider label should appear in the view
    }
    
    /** @test */
    public function it_can_save_resources()
    {
        // Test saving a resource
        $model = new DummyModel(['name' => 'Test Model']);
        
        Livewire::test(MyNewProviderLwComponent::class, [
            'model' => $model,
            'search' => 'test query'
        ])
        ->call('saveResource', 'test_id', 'https://example.com/resource/test_id')
        ->assertEmitted('resourcesChanged');
        
        // Check that the resource was saved
        $this->assertCount(1, $model->resources);
        $this->assertEquals('MyNewProvider', $model->resources[0]->provider);
    }
}
```

## Advanced Provider Features

### Handling Authentication

If your provider requires authentication:

```php
private function fetchDataFromApi(string $search, array $options)
{
    $client = new \GuzzleHttp\Client();
    
    // Get API credentials from config or environment
    $apiKey = config('resources-components.providers.my-new-provider.api_key') ?? env('MY_PROVIDER_API_KEY');
    
    return $client->get('https://api.example.com/search', [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey
        ],
        'query' => [
            'q' => $search,
            'limit' => $options['limit']
        ]
    ]);
}
```

### Storing Additional Data

If your provider returns useful data that should be stored with the resource:

```php
public function saveResource($provider_id, $url, $full_json = null)
{
    $data = [
        'provider' => $this->provider,
        'provider_id' => $provider_id,
        'url' => $url,
        'data' => json_decode($full_json, true) // Store additional data
    ];
    
    $resource = $this->model->{$this->saveMethod}($data);
    $this->dispatch('resourcesChanged');
    event(new ResourceSaved($resource, $this->model->id));
}
```

Then in your view:

```blade
'saveAction' => function($result) use ($base_url) {
    // Pass the full JSON data as the third parameter
    return "saveResource('{$result->id}', '{$base_url}{$result->path}', '" . htmlspecialchars(json_encode($result), ENT_QUOTES, 'UTF-8') . "')";
},
```

## Best Practices

1. **Error Handling**: Always include proper error handling in your provider.
2. **Rate Limiting**: Respect the rate limits of the external API you're integrating with.
3. **Caching**: Consider adding caching for frequent searches to reduce API calls.
4. **Standardized Results**: Process API results into a consistent format for simpler view rendering.
5. **Documentation**: Add phpDoc comments to your methods and update the README.md with information about your provider.

## Contribution

When contributing a new provider to the main package:

1. Follow the coding style of the existing package.
2. Write comprehensive tests that cover all aspects of your provider.
3. Update the README.md to document your provider's capabilities.
4. Consider adding an example to the documentation.
