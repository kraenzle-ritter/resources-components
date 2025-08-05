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

        // Ensure the providerKey is valid
        if (empty($providerKey) || !is_string($providerKey) || strpos($providerKey, 'wikipedia-') !== 0) {
            $providerKey = 'wikipedia-de'; // Fallback to German
        }

        // Read base_url from configuration
        $apiUrl = config('resources-components.providers.' . $providerKey . '.base_url');

        if (!$apiUrl) {
            // Fallback if no API URL is found in the configuration
            $locale = substr($providerKey, strlen('wikipedia-'));
            $apiUrl = "https://{$locale}.wikipedia.org/w/api.php";
        }

        $this->base_url = str_replace('/w/api.php', '/wiki/', $apiUrl);

        $this->search = trim($search) ?: '';

        // Use the providerKey directly for API requests
        $this->queryOptions = [];
        $this->queryOptions['providerKey'] = $providerKey; // Important: Pass the providerKey instead of locale
        $this->queryOptions['limit'] = 5;
    }

    public function saveResource($provider_id, $url, $title = null)
    {
        // Check if a target_url is defined in the configuration
        $targetUrlTemplate = config("resources-components.providers.{$this->queryOptions['providerKey']}.target_url");

        if ($targetUrlTemplate && $title) {
            // Replace spaces with underscores for the article name
            $underscoredName = str_replace(' ', '_', $title);

            // Platzhalter im Template ersetzen
            $url = str_replace(
                ['{provider_id}', '{underscored_name}'],
                [$provider_id, $underscoredName],
                $targetUrlTemplate
            );
        } else {
            // Fallback auf die bisherige URL-Generierung
            $url = preg_replace_callback('/ /', function($match) {
                return '_';
            }, $url);
        }

        $data = [
            'provider' => $this->queryOptions['providerKey'], // Use the specific providerKey instead of generic 'Wikipedia'
            'provider_id' => $provider_id,
            'url' => $url
        ];

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

        // Perform the search
        $resources = $client->search($this->search, $this->queryOptions);

        // Process the results: Extract the first sentence for each result
        if (!empty($resources)) {
            foreach ($resources as $key => $result) {
                if (!empty($result->snippet)) {
                    // Bereinige HTML-Tags und spezielle Formate vor der Verarbeitung
                    $cleanedSnippet = html_entity_decode(strip_tags($result->snippet));

                    // Particularly thorough cleanup for Wikipedia snippets
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
            'base_url' => $this->base_url // Pass the base_url to the view
        ]);
    }
}
