<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;

class IdiotikonLwComponent extends Component
{
    use ProviderComponentTrait;
    public $search;

    public $queryOptions;

    public $model;

    public $endpoint;

    public $resourceable_id;

    public $provider = 'Idiotikon';

    public $saveMethod = 'updateOrCreateResource'; // Method name for saving resources

    public $removeMethod = 'removeResource'; // Method name for resource removal

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $this->search = trim($search) ?: '';

        $this->queryOptions = $params['queryOptions'] ?? ['limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $full_json = preg_replace('/[\x00-\x1F]/','', $full_json);
        \Log::debug(json_decode(json_last_error()));
        
        // Prüfe, ob eine target_url in der Konfiguration definiert ist
        $targetUrlTemplate = config("resources-components.providers.idiotikon.target_url");
        
        if ($targetUrlTemplate) {
            // Platzhalter im Template ersetzen
            $url = str_replace('{provider_id}', $provider_id, $targetUrlTemplate);
            
            if (class_exists('\Log')) {
                \Log::debug('IdiotikonLwComponent using target_url template: ' . $targetUrlTemplate);
                \Log::debug('IdiotikonLwComponent generated URL: ' . $url);
            }
        }

        $data = [
            'provider' => 'idiotikon',
            'provider_id' => $provider_id,
            'url' => $url,
            'full_json' => json_decode($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);
        $this->dispatch('resourcesChanged');
        event(new ResourceSaved($resource, $this->model->id));
    }

    public function removeResource($url)
    {
        Resource::where([
            'url' => $url
        ])->delete();
        $this->dispatch('resourcesChanged');
    }

    public function render()
    {
        $client = new Idiotikon();

        $resources = $client->search($this->search, $this->queryOptions);

        // Verarbeite die Ergebnisse mit dem ProviderComponentTrait
        if (!empty($resources)) {
            foreach ($resources as $key => $result) {
                // Verarbeite Beschreibungen, falls vorhanden
                if (!empty($result->description) && !empty($result->description[0])) {
                    $result->processedDescription = $this->extractFirstSentence($result->description[0]);
                } else {
                    $result->processedDescription = "ID: idiotikon-{$result->lemmaID}";
                }
            }
        }

        $view = view()->exists('vendor.kraenzle-ritter.livewire.idiotikon-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.idiotikon-lw-component'
              : 'resources-components::livewire.idiotikon-lw-component';

        if (!isset($resources) or !count($resources)) {
            return view($view, [
                'results' => []
            ]);
        }

        return view($view, [
            'results' => $resources
        ]);
    }
}
