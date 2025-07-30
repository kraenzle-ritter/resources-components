<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class WikidataLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'Wikidata';

    public $saveMethod = 'updateOrCreateResource'; // Method name for saving resources

    public $removeMethod = 'removeResource'; // Method name for resource removal

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount ($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $this->search = trim($search) ?: '';

        $this->queryOptions = $params['queryOptions'] ?? ['locale' => 'de', 'limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => $url,
            'full_json' => json_encode($full_json, JSON_UNESCAPED_UNICODE)
        ];
        $resource = $this->model->{$this->saveMethod}($data);
        $this->model->saveMoreResources('wikidata');

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
        $client = new Wikidata();

        $resources = $client->search($this->search, $this->queryOptions);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.wikidata-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.wikidata-lw-component'
              : 'resources-components::livewire.wikidata-lw-component';

        return view($view, [
            'results' => $resources ?: null
        ]);
    }

}
