# Development Guide

Kurzanleitung für die Entwicklung neuer Provider für das `resources-components` Package.

## Provider-Struktur

Ein neuer Provider benötigt:

1. **Provider-Klasse** (`MyNewProvider.php`) - API-Kommunikation
2. **Livewire-Component** (`MyNewProviderLwComponent.php`) - UI-Logik
3. **View-Template** (`my-new-provider-lw-component.blade.php`) - Frontend
4. **Konfiguration** in `resources-components.php`

## 1. Provider-Klasse

```php
<?php
namespace KraenzleRitter\ResourcesComponents;

class MyNewProvider
{
    public function search(string $search, array $params = [])
    {
        $limit = $params['limit'] ?? config('resources-components.limit', 5);
        
        // API-Aufruf implementieren
        $rawResults = $this->fetchFromApi($search, $limit);
        
        // Ergebnisse standardisieren (id, title, snippet/description)
        return $this->processResults($rawResults);
    }
    
    private function fetchFromApi(string $search, int $limit)
    {
        // GuzzleHttp oder andere HTTP-Client verwenden
        // return $client->get('https://api.example.com/search?q=' . urlencode($search));
    }
    
    private function processResults($results)
    {
        // API-spezifische Datenstruktur in Standard-Format umwandeln
        return array_map(function($item) {
            return (object)[
                'id' => $item->id,
                'title' => $item->name,
                'snippet' => $item->description ?? '',
                // weitere Felder nach Bedarf
            ];
        }, $results);
    }
}
```

## 2. Livewire Component

```php
<?php
namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class MyNewProviderLwComponent extends Component
{
    use ProviderComponentTrait;
    
    public $search;
    public $model;
    public $provider = 'MyNewProvider';
    public $base_url;
    
    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', string $providerKey = 'my-new-provider')
    {
        $this->model = $model;
        $this->search = trim($search);
        $this->base_url = config("resources-components.providers.{$providerKey}.base_url");
    }
    
    public function render()
    {
        $client = new MyNewProvider();
        $results = $client->search($this->search, ['limit' => 5]);
        
        return view('resources-components::livewire.my-new-provider-lw-component', [
            'results' => $results,
            'model' => $this->model
        ]);
    }
}
```

## 3. View Template

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
        $output = "<a href=\"{$base_url}{$result->path}\" target=\"_blank\">{$result->title}</a>";
        if (!empty($result->snippet)) {
            $output .= "<br>" . $result->snippet;
        }
        return $output;
    }
])
```

## 4. Konfiguration

In `config/resources-components.php`:

```php
'providers' => [
    'my-new-provider' => [
        'label' => 'My New Provider',
        'api-type' => 'MyNewProvider',
        'base_url' => 'https://api.example.com/',
        'api_key' => env('MY_PROVIDER_API_KEY', ''),
        'test_search' => 'test query', // Für Provider-Check-Seite
    ],
]
```

## 5. Service Provider Registration

In `ResourcesComponentsServiceProvider.php`:

```php
public function boot()
{
    Livewire::component('my-new-provider-lw-component', MyNewProviderLwComponent::class);
}
```

## Testing

Basis-Test in `tests/Feature/MyNewProviderTest.php`:

```php
<?php
namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\MyNewProvider;
use Livewire\Livewire;

class MyNewProviderTest extends TestCase
{
    /** @test */
    public function it_can_search_and_return_results()
    {
        $provider = new MyNewProvider();
        $results = $provider->search('test');
        
        $this->assertNotEmpty($results);
        $this->assertObjectHasAttribute('id', $results[0]);
        $this->assertObjectHasAttribute('title', $results[0]);
    }
    
    /** @test */
    public function livewire_component_works()
    {
        $model = new \KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel();
        
        Livewire::test(\KraenzleRitter\ResourcesComponents\MyNewProviderLwComponent::class, [
            'model' => $model,
            'search' => 'test'
        ])->assertViewIs('resources-components::livewire.my-new-provider-lw-component');
    }
}
```

## Erweiterte Features

### Authentifizierung

```php
private function fetchFromApi(string $search, int $limit)
{
    $apiKey = config('resources-components.providers.my-new-provider.api_key');
    
    return $client->get('https://api.example.com/search', [
        'headers' => ['Authorization' => 'Bearer ' . $apiKey],
        'query' => ['q' => $search, 'limit' => $limit]
    ]);
}
```

### Zusätzliche Daten speichern

```php
public function saveResource($provider_id, $url, $full_json = null)
{
    $data = [
        'provider' => $this->provider,
        'provider_id' => $provider_id,
        'url' => $url,
        'data' => $full_json ? json_decode($full_json, true) : null
    ];
    
    $resource = $this->saveResourceToModel($this->model, $data);
    $this->dispatch('resourcesChanged');
    event(new ResourceSaved($resource, $this->model->id));
}
```

## Best Practices

- **Fehlerbehandlung**: Try-catch für API-Aufrufe verwenden
- **Rate Limiting**: API-Limits respektieren
- **Caching**: Häufige Suchanfragen cachen
- **Konsistente Datenstruktur**: Standardisierte Ergebnisformate verwenden
- **Tests**: Umfassende Tests für alle Provider-Funktionen schreiben
