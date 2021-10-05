<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Ortsnamen;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class OrtsnamenLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $endpoint;

    public $resourceable_id;

    public $provider = 'Ortsnamen';

    public $saveMethod = 'updateOrCreateResource';

    public $removeMethod = 'removeResource'; // url

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $this->search = trim($search) ?: 'Zürich';

        $this->queryOptions = $params['queryOptions'] ?? ['limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $full_json  = preg_replace('/[\x00-\x1F]/','', $full_json);
        \Log::debug(json_decode(json_last_error()));

        $data = [
            'provider' => 'ortsnamen',
            'provider_id' => $provider_id,
            'url' => $url,
            'full_json' => json_decode($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);
        $this->emit('resourcesChanged');
        event(new ResourceSaved($resource, $this->model->id));
    }

    public function removeResource($url)
    {
        Resource::where([
            'url' => $url
        ])->delete();
        $this->emit('resourcesChanged');
    }

    public function render()
    {
        $client = new Ortsnamen();

        $resources = $client->search($this->search, $this->queryOptions, $this->endpoint);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.ortsnamen-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.ortsnamen-lw-component'
              : 'resources-components::ortsnamen-lw-component';

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
