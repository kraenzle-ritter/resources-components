<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;

class WikipediaLwComponent extends Component
{
    use ProviderComponentTrait;
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'Wikipedia';

    public $base_url;

    public $saveMethod = 'updateOrCreateResource'; // Method name for saving resources

    public $removeMethod = 'removeResource'; // Method name for resource removal

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', string $providerKey = 'wikipedia-de')
    {
        $this->model = $model;

        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent mount: Received parameters:');
            \Log::debug('- model: ' . get_class($model));
            \Log::debug('- search: ' . $search);
            \Log::debug('- providerKey: ' . $providerKey);
        }

        // Stelle sicher, dass der providerKey gültig ist
        if (empty($providerKey) || !is_string($providerKey) || strpos($providerKey, 'wikipedia-') !== 0) {
            $providerKey = 'wikipedia-de'; // Fallback auf Deutsch
            if (class_exists('\Log')) {
                \Log::warning('WikipediaLwComponent: Invalid providerKey, using default: wikipedia-de');
            }
        }

        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent: Using providerKey: ' . $providerKey);
        }

        // Read base_url from configuration
        $apiUrl = config('resources-components.providers.' . $providerKey . '.base_url');

        // Debug-Ausgabe für die Konfiguration
        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent: Config path: resources-components.providers.' . $providerKey . '.base_url');
            \Log::debug('WikipediaLwComponent: Config value: ' . ($apiUrl ?: 'NULL'));

            // Liste alle verfügbaren Provider-Konfigurationen auf
            $providers = config('resources-components.providers');
            if ($providers) {
                \Log::debug('WikipediaLwComponent: Available provider configs: ' . implode(', ', array_keys($providers)));
            } else {
                \Log::warning('WikipediaLwComponent: No providers found in config');
            }
        }

        if (!$apiUrl) {
            // Fallback, wenn keine API-URL in der Konfiguration gefunden wird
            $locale = substr($providerKey, strlen('wikipedia-'));
            $apiUrl = "https://{$locale}.wikipedia.org/w/api.php";
            if (class_exists('\Log')) {
                \Log::debug('WikipediaLwComponent: No API URL found in config, using fallback: ' . $apiUrl);
            }
        }

        $this->base_url = str_replace('/w/api.php', '/wiki/', $apiUrl);
        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent: Set base_url: ' . $this->base_url);
        }

        $this->search = trim($search) ?: '';

        // Verwende den providerKey direkt für die API-Anfragen
        $this->queryOptions = [];
        $this->queryOptions['providerKey'] = $providerKey; // Wichtig: Übergebe den providerKey statt locale
        $this->queryOptions['limit'] = 5;

        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent: providerKey: ' . $providerKey);
            \Log::debug('WikipediaLwComponent: QueryOptions: ', $this->queryOptions);
        }
    }

    public function saveResource($provider_id, $url)
    {
        // Debug-Ausgabe für die URL, die gespeichert wird
        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent saveResource URL before: ' . $url);
        }

        // Korrekte URL-Kodierung: Ersetze Leerzeichen durch Unterstriche und kodiere URL-unsichere Zeichen
        $encodedUrl = preg_replace_callback('/ /', function($match) {
            return '_';
        }, $url);

        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => $encodedUrl
        ];

        // Debug-Ausgabe für die verarbeitete URL
        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent saveResource URL after: ' . $data['url']);

            // Prüfe, ob die URL für die richtige Sprache ist
            if (preg_match('/https?:\/\/([a-z]{2})\.wikipedia\.org\/wiki\//', $data['url'], $matches)) {
                $language = $matches[1];
                \Log::debug('WikipediaLwComponent saveResource language detected: ' . $language);
            } else {
                \Log::warning('WikipediaLwComponent saveResource could not detect language from URL: ' . $data['url']);
            }
        }

        $resource = $this->model->{$this->saveMethod}($data);
        $this->model->saveMoreResources('wikipedia');
        $this->dispatch('resourcesChanged');
        event(new ResourceSaved($resource, $this->model->id));
    }

    public function removeResource($url)
    {
        \KraenzleRitter\Resources\Resource::where([
            'url' => $url
        ])->delete();
        $this->dispatch('resourcesChanged');
    }

    public function render()
    {
        $client = new Wikipedia();

        // Ensure the correct locale is used
        if (class_exists('\Log')) {
            \Log::debug('WikipediaLwComponent render: base_url = ' . $this->base_url);
            \Log::debug('WikipediaLwComponent render: queryOptions = ', $this->queryOptions);
        }

        // Perform the search
        $resources = $client->search($this->search, $this->queryOptions);

        // Verarbeite die Ergebnisse: Extrahiere den ersten Satz für jedes Ergebnis
        if (!empty($resources)) {
            foreach ($resources as $key => $result) {
                if (!empty($result->snippet)) {
                    // Bereinige HTML-Tags und spezielle Formate vor der Verarbeitung
                    $cleanedSnippet = html_entity_decode(strip_tags($result->snippet));

                    // Besonders gründliche Bereinigung für Wikipedia-Snippets
                    $cleanedSnippet = preg_replace('/\} \]\)|\[\[.*?\]\]|<.*?>|\{\{.*?\}\}/', '', $cleanedSnippet);

                    // Formatiere oder entferne weitere Wikipedia-spezifische Markup-Elemente
                    $cleanedSnippet = preg_replace('/&lt;.*?&gt;/', '', $cleanedSnippet);
                    $cleanedSnippet = preg_replace('/\{\|.*?\|\}/', '', $cleanedSnippet);

                    // Doppelte Leerzeichen entfernen und Text trimmen
                    $cleanedSnippet = preg_replace('/\s+/', ' ', $cleanedSnippet);
                    $cleanedSnippet = trim($cleanedSnippet);

                    // Versuche nun den ersten Satz zu extrahieren
                    $resources[$key]->firstSentence = $this->extractFirstSentence($cleanedSnippet);

                    // Stelle sicher, dass der Text bei einem Satzende endet
                    if (!empty($resources[$key]->firstSentence) && !preg_match('/[.!?]$/', $resources[$key]->firstSentence)) {
                        $resources[$key]->firstSentence .= '.';
                    }
                } else {
                    $resources[$key]->firstSentence = '';
                }
            }
        }

        // Choose the appropriate view
        $view = view()->exists('vendor.kraenzle-ritter.livewire.wikipedia-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.wikipedia-lw-component'
              : 'resources-components::livewire.wikipedia-lw-component';

        return view($view, [
            'results' => $resources,
            'base_url' => $this->base_url // Übergebe die base_url an die View
        ]);
    }
}
