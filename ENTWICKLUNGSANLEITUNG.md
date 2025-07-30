# Entwicklungsleitfaden für resources-components

## Einführung

Dieses Dokument beschreibt, wie man neue Provider für das `kraenzle-ritter/resources-components` Paket entwickelt. Die Provider ermöglichen die Suche nach Ressourcen in externen Datenbanken und API-Diensten.

## Architektur

Das Paket ist wie folgt aufgebaut:

1. **Provider-Klassen**: Verarbeiten API-Anfragen und formatieren die Ergebnisse
2. **Livewire-Komponenten**: Bieten eine Benutzeroberfläche für die Provider
3. **Service Provider**: Registrieren die Komponenten und Dienste
4. **Konfiguration**: Definiert die verfügbaren Provider und deren Einstellungen

## Einen neuen Provider erstellen

### Schritt 1: Provider-Klasse erstellen

Erstellen Sie eine neue Klasse, die `AbstractProvider` erweitert und das `ProviderInterface` implementiert:

```php
<?php

namespace KraenzleRitter\ResourcesComponents\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MeinNeuerProvider extends AbstractProvider
{
    protected $client;
    
    public function __construct(string $providerKey, array $config = [])
    {
        parent::__construct($providerKey, $config);
        
        // Client mit der konfigurierten Basis-URL initialisieren
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }
    
    /**
     * Sucht nach Ressourcen beim externen Dienst
     */
    public function search(string $search, array $params = [])
    {
        $limit = $params['limit'] ?? 5;
        
        try {
            // API-Anfrage stellen
            $response = $this->client->request('GET', 'search', [
                'query' => [
                    'q' => $search,
                    'limit' => $limit,
                    // Weitere API-spezifische Parameter
                ]
            ]);
            
            // Antwort als JSON-Objekt zurückgeben
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    /**
     * Verarbeitet die Suchergebnisse in ein standardisiertes Format
     */
    public function processResult($results): array
    {
        if (!$results) {
            return [];
        }
        
        $processed = [];
        
        foreach ($results->items as $item) {
            $processed[] = [
                'title' => $item->title,
                'description' => $item->description ?? '',
                'url' => $item->url,
                'raw_data' => json_encode($item)
            ];
        }
        
        return $processed;
    }
}
```

### Schritt 2: Livewire-Komponente erstellen

Erstellen Sie eine Livewire-Komponente zur Interaktion mit dem Provider:

```php
<?php

namespace KraenzleRitter\ResourcesComponents\Components;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Providers\MeinNeuerProvider;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;

class MeinNeuerLivewireComponent extends Component
{
    use ProviderComponentTrait;
    
    public $search;
    public $model;
    public $providerKey = 'mein-neuer-provider';
    public $providerLabel = 'Mein Neuer Provider';
    public $results = [];
    public $error = null;
    
    protected $provider;
    protected $listeners = ['resourcesChanged' => 'render'];
    
    /**
     * Komponente initialisieren
     */
    public function mount($model, string $search = '', string $providerKey = 'mein-neuer-provider')
    {
        $this->model = $model;
        $this->search = $search;
        $this->providerKey = $providerKey;
        
        // Provider-Konfiguration aus config laden oder Standardwerte verwenden
        $config = config('resources-components.providers.' . $providerKey) ?? [
            'base_url' => 'https://api.beispiel.com/',
            'label' => $this->providerLabel
        ];
        
        // Provider-Instanz erstellen
        $this->provider = new MeinNeuerProvider($providerKey, $config);
    }
    
    /**
     * Suche ausführen
     */
    public function search()
    {
        if (empty($this->search)) {
            $this->results = [];
            $this->error = null;
            return;
        }
        
        try {
            $params = [
                'limit' => config('resources-components.limit', 5)
            ];
            
            $rawResults = $this->provider->search($this->search, $params);
            
            if ($rawResults === null) {
                $this->error = 'Fehler bei der Verbindung zum Provider. Bitte versuchen Sie es erneut.';
                $this->results = [];
                return;
            }
            
            $this->results = $this->provider->processResult($rawResults);
            $this->error = null;
        } catch (\Exception $e) {
            $this->handleSearchError($e);
        }
    }
    
    /**
     * Ressource aus Suchergebnissen speichern
     */
    public function saveResource(string $title, string $url, ?string $rawData = null)
    {
        try {
            $resourceData = [
                'provider' => $this->providerKey,
                'title' => $title,
                'url' => $url
            ];
            
            if ($rawData) {
                $resourceData['data'] = $rawData;
            }
            
            // Ressource mit der Trait-Methode speichern
            $resource = $this->saveResourceToModel($this->model, $resourceData);
            
            // Suche zurücksetzen
            $this->search = '';
            $this->results = [];
            
            // Event auslösen
            event(new ResourceSaved($resource, $this->model->id));
            
            // Erfolgs-Nachricht anzeigen
            session()->flash('message', 'Ressource erfolgreich gespeichert.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Speichern der Ressource: ' . $e->getMessage());
        }
    }
    
    /**
     * Komponente rendern
     */
    public function render()
    {
        return view('resources-components::livewire.mein-neuer-provider', [
            'model' => $this->model,
            'results' => $this->results,
            'error' => $this->error
        ]);
    }
}
```

### Schritt 3: Blade-Template erstellen

Erstellen Sie ein Blade-Template für die Komponente unter `resources/views/livewire/mein-neuer-provider.blade.php`:

```blade
<div>
    <div class="form-group">
        <label for="search">{{ __('resources-components::messages.search') }}</label>
        <div class="input-group">
            <input type="text" class="form-control" id="search" placeholder="{{ __('resources-components::messages.search_placeholder') }}" wire:model.defer="search">
            <div class="input-group-append">
                <button class="btn btn-primary" wire:click="search">
                    <span wire:loading.remove wire:target="search">
                        {{ __('resources-components::messages.search_button') }}
                    </span>
                    <span wire:loading wire:target="search">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </div>
    </div>
    
    @if($error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endif
    
    @if(count($results))
        <div class="results-container mt-4">
            <h4>{{ __('resources-components::messages.results') }}</h4>
            <ul class="list-group">
                @foreach($results as $result)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>{{ $result['title'] }}</h5>
                                @if(isset($result['description']))
                                    <p>{{ $result['description'] }}</p>
                                @endif
                                <a href="{{ $result['url'] }}" target="_blank" class="small">{{ $result['url'] }}</a>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success" wire:click="saveResource('{{ addslashes($result['title']) }}', '{{ $result['url'] }}', '{{ isset($result['raw_data']) ? addslashes($result['raw_data']) : null }}')">
                                    {{ __('resources-components::messages.save') }}
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @elseif($search && !count($results))
        <div class="alert alert-info mt-3">
            {{ __('resources-components::messages.no_results') }}
        </div>
    @endif
</div>
```

### Schritt 4: Provider in Konfiguration registrieren

Fügen Sie Ihren Provider zur `config/resources-components.php` hinzu:

```php
'mein-neuer-provider' => [
    'label' => 'Mein Neuer Provider',
    'api-type' => 'MeinNeuer',
    'base_url' => 'https://api.beispiel.com/',
    'api_key' => env('MEIN_PROVIDER_API_KEY', ''),
    'provider_class' => \KraenzleRitter\ResourcesComponents\Providers\MeinNeuerProvider::class,
],
```

### Schritt 5: Komponente im Service Provider registrieren

Registrieren Sie Ihre Komponente im `ResourcesComponentsServiceProvider`:

```php
// In der boot-Methode
Livewire::component('mein-neuer-lw-component', MeinNeuerLivewireComponent::class);

// In der provides-Methode
public function provides()
{
    return [
        // ...andere Komponenten
        'mein-neuer-lw-component',
    ];
}
```

## Tests schreiben

Für jeden Provider sollten Sie Unit-Tests und Feature-Tests schreiben:

### Unit-Test für den Provider

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Providers\MeinNeuerProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class MeinNeuerProviderTest extends TestCase
{
    public function testSearch()
    {
        // Mock-Antwort für den HTTP-Client
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'items' => [
                    [
                        'title' => 'Test Titel',
                        'description' => 'Test Beschreibung',
                        'url' => 'https://example.com/test'
                    ]
                ]
            ]))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Provider mit Mock-Client erstellen
        $provider = $this->getMockBuilder(MeinNeuerProvider::class)
            ->setConstructorArgs(['mein-neuer-provider', []])
            ->setMethods(['getClient'])
            ->getMock();
            
        $provider->method('getClient')->willReturn($client);
        
        // Suche ausführen
        $results = $provider->search('test', ['limit' => 5]);
        
        // Ergebnisse verarbeiten
        $processed = $provider->processResult($results);
        
        // Assertions
        $this->assertCount(1, $processed);
        $this->assertEquals('Test Titel', $processed[0]['title']);
        $this->assertEquals('Test Beschreibung', $processed[0]['description']);
        $this->assertEquals('https://example.com/test', $processed[0]['url']);
    }
}
```

### Feature-Test für die Livewire-Komponente

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use KraenzleRitter\ResourcesComponents\Components\MeinNeuerLivewireComponent;

class MeinNeuerLivewireComponentTest extends TestCase
{
    /** @test */
    public function it_renders_correctly()
    {
        $user = User::factory()->create();
        
        Livewire::test(MeinNeuerLivewireComponent::class, [
            'model' => $user,
            'search' => '',
            'providerKey' => 'mein-neuer-provider'
        ])
        ->assertViewIs('resources-components::livewire.mein-neuer-provider')
        ->assertSeeHtml('search');
    }
    
    /** @test */
    public function it_searches_and_shows_results()
    {
        $user = User::factory()->create();
        
        // Mock für den Provider erstellen
        $mockProvider = $this->getMockBuilder(MeinNeuerProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['search', 'processResult'])
            ->getMock();
            
        $mockProvider->expects($this->once())
            ->method('search')
            ->willReturn(['example_result']);
            
        $mockProvider->expects($this->once())
            ->method('processResult')
            ->willReturn([
                [
                    'title' => 'Test Titel',
                    'description' => 'Test Beschreibung',
                    'url' => 'https://example.com/test'
                ]
            ]);
        
        // Komponente mit Mock-Provider testen
        Livewire::test(MeinNeuerLivewireComponent::class, [
            'model' => $user,
            'search' => 'test',
            'providerKey' => 'mein-neuer-provider'
        ])
        ->set('provider', $mockProvider)
        ->call('search')
        ->assertSee('Test Titel')
        ->assertSee('Test Beschreibung')
        ->assertSee('https://example.com/test');
    }
}
```

## Übersetzungen

Fügen Sie Übersetzungen für Ihren Provider in den Sprachdateien hinzu:

### resources/lang/de/messages.php

```php
<?php

return [
    'search' => 'Suche',
    'search_placeholder' => 'Suchbegriff eingeben...',
    'search_button' => 'Suchen',
    'results' => 'Ergebnisse',
    'no_results' => 'Keine Ergebnisse gefunden',
    'save' => 'Speichern',
    'mein_neuer_provider' => 'Mein Neuer Provider',
];
```

## Dokumentation

Dokumentieren Sie Ihren Provider in der README.md des Projekts:

```markdown
## Mein Neuer Provider

Der Mein Neuer Provider ermöglicht die Suche nach Ressourcen in der Beispiel-API.

### Konfiguration

```php
'mein-neuer-provider' => [
    'label' => 'Mein Neuer Provider',
    'api-type' => 'MeinNeuer',
    'base_url' => 'https://api.beispiel.com/',
    'api_key' => env('MEIN_PROVIDER_API_KEY', ''),
],
```

### Umgebungsvariablen

```
MEIN_PROVIDER_API_KEY=your_api_key_here
```

### Verwendung

```php
// In einem Controller
$providers = ['mein-neuer-provider'];
```

```blade
<!-- In einer Blade-Vorlage -->
<livewire:provider-select :model="$model" :providers="$providers" />
```
```

## Best Practices

1. **Fehlerbehandlung**: Fangen Sie alle API-Fehler ab und protokollieren Sie sie.
2. **Konfigurierbarkeit**: Machen Sie API-Endpunkte und Limits konfigurierbar.
3. **Caching**: Implementieren Sie Caching für häufige Abfragen.
4. **Testbarkeit**: Schreiben Sie Tests für alle Provider und Komponenten.
5. **Dokumentation**: Dokumentieren Sie alle verfügbaren Parameter und Konfigurationsoptionen.
