<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class ManualInputLwComponent extends Component
{
    public $provider;

    public $provider_id;

    public $url;

    public $model;

    public $resourceable_id;

    public $saveMethod = 'updateOrCreateResource';

    public $removeMethod = 'removeResource'; // url

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;
    }

    public function saveResource()
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'url' => $this->url,
        ];

        $resource = $this->model->{$this->saveMethod}($data);
        $this->model->saveMoreResources('gnd');

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
        $view = view()->exists('vendor.kraenzle-ritter.livewire.manual-input-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.manual-input-lw-component'
              : 'resources-components::manual-input-lw-component';

        return view($view);
    }
}
