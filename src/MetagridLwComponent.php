<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Metagrid;

class MetagridLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'Metagrid';

    public $saveMethod = 'updateOrCreateResource'; // (id, url, full_json)

    public $removeMethod = 'removeResource'; // url

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount ($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $locale = $params['locale'] ?? 'de';

        $this->search = trim($search) ?: 'Cassirer';

        $this->queryOptions = $params['queryOptions'] ?? ['locale' => $locale, 'limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => $url
        ];
        $this->model->{$this->saveMethod}($data);
        $full_json = json_decode($full_json);
        $this->model->{$this->saveMethod}($data);

        $data = null;
        if (isset($full_json->resources)) {
            foreach($full_json->resources as $resource) {
                $data = [
                    'provider' => $resource->provider->slug,
                    'provider_id' => $resource->identifier,
                    'url' => $resource->link->uri,
                ];
                $this->model->{$this->saveMethod}($data);
            }
        }
        $this->emit('resourcesChanged');
    }

    public function removeResource($url)
    {
        \KraenzleRitter\Resources\Resource::where([
            'url' => $url
        ])->delete();
        $this->emit('resourcesChanged');
    }

    public function render()
    {
        $client = new Metagrid();

        $resources = $client->search($this->search, $this->queryOptions);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.metagrid-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.metagrid-lw-component'
              : 'resources-components::metagrid-lw-component';

        return view($view, [
            'results' => $resources
        ]);
    }

}
