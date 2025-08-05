<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use Illuminate\Support\Str;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
class AntonLwComponent extends Component
{
    public $search;
    public $queryOptions;
    public $model;
    public $endpoint;
    public $resourceable_id;
    public string $providerKey; // Provider key: Georgfischer, Gosteli, KBA
    public $saveMethod = 'updateOrCreateResource';
    public $removeMethod = 'removeResource'; // Method name for resource removal
    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', string $providerKey,  string $endpoint, array $params = [])
    {
        $this->model = $model;

        $this->providerKey = $providerKey;

        $this->search = trim($search);

        $this->endpoint = $endpoint;

        $this->queryOptions = $params['queryOptions'] ?? ['limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        // Try to get the slug from the configuration
        $slug = config("resources-components.providers.{$this->providerKey}.slug");

        // Try to get a target_url from the configuration
        $targetUrlTemplate = config("resources-components.providers.{$this->providerKey}.target_url");

        // Extrahiere die reine ID aus der provider_id
        // Die provider_id hat das Format "slug-endpoint-id", z.B. "gfa-actors-37"
        $idParts = explode('-', $provider_id);
        $shortProviderId = end($idParts); // Letzte Komponente ist die ID

        if ($targetUrlTemplate) {
            // Ersetze die Platzhalter in der target_url
            $url = str_replace(
                ['{endpoint}', '{short_provider_id}', '{provider_id}', '{slug}'],
                [$this->endpoint, $shortProviderId, $provider_id, $slug],
                $targetUrlTemplate
            );
        } else {
            // Fallback: Verwende die bisherige URL-Generierungsmethode
            $base_url = Str::finish(config("resources-components.providers.{$this->providerKey}.base_url"), '/');
            $url = $base_url . $this->endpoint . '/' . $shortProviderId;
        }

        $data = [
            'provider' => $this->providerKey,
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
        $client = new Anton($this->providerKey);

        $resources = $client->search($this->search, $this->queryOptions, $this->endpoint);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.anton-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.anton-lw-component'
              : 'resources-components::livewire.anton-lw-component';

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
