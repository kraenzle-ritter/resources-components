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
        $base_url = Str::finish(config("resources-components.providers.{$this->providerKey}.base_url"), '/');

        $data = [
            'provider' => $this->providerKey,
            'provider_id' => $provider_id,
            'url' => $base_url . $this->endpoint . '/' . $provider_id,
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
