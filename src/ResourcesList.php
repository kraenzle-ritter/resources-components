<?php

namespace KraenzleRitter\ResourcesComponents;;

use Livewire\Component;

class ResourcesList extends Component
{
    public $model;

    public $deleteButton;

    public $resources;

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, $deleteButton = false)
    {
        $this->model = $model;
        $this->deleteButton = $deleteButton;
    }

    public function removeResource($id)
    {
        $this->model->removeResource($id);
        $this->dispatch('resourcesChanged');
    }

    public function render()
    {
        $this->model->load('resources');

        $this->resources = $this->model->resources; //->sortBy('provider');

        $view = view()->exists('vendor.kraenzle-ritter.livewire.resources-list')
              ? 'vendor.kraenzle-ritter.livewire.resources-list'
              : 'resources-components::resources-list';

        return view($view);
    }
}
