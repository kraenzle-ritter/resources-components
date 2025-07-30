<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Geonames;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class GeonamesLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'Geonames';

    public $saveMethod = 'updateOrCreateResource'; // Method name for saving resources

    public $removeMethod = 'removeResource'; // Method name for resource removal

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $this->search = trim($search) ?: 'Cassirer';

        $this->queryOptions = $params['queryOptions'] ?? ['limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
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
        $client = new Geonames();

        $resources = $client->search($this->search, $this->queryOptions);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.geonames-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.geonames-lw-component'
              : 'resources-components::livewire.geonames-lw-component';

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
