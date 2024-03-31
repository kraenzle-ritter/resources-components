<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
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

    protected $rules = [
        'provider' => 'string',
        'provider_id' => 'string',
        'url' => 'required|url'
    ];

    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function saveResource()
    {
        $data= $this->validate();
        $resource = $this->model->{$this->saveMethod}($data);

        $this->dispatch('resourcesChanged');
        event(new ResourceSaved($resource, $this->model->id));
    }

    public function render()
    {
        $view = view()->exists('vendor.kraenzle-ritter.livewire.manual-input-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.manual-input-lw-component'
              : 'resources-components::manual-input-lw-component';

        return view($view);
    }
}
