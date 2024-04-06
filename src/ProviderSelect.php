<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;

class ProviderSelect extends Component
{
    public $providers;

    public $providers_all;

    public $provider;

    public $endpoint;

    public $model;

    public $listeners = ['resourcesChanged' => 'hydrate'];

    public function mount($model, $providers, $endpoint)
    {
        //logger(__METHOD__, [$this->provider]);
        $this->model = $model;
        $this->endpoint = $endpoint;
        $this->providers_all = array_map('strtolower', $providers);
        $linked_providers = $this->model->resources->pluck('provider')->toArray();
        $this->providers = array_values(array_diff($this->providers_all, $linked_providers));
        $this->provider = $this->providers[0] ?? '';
    }

    public function hydrate()
    {
        //logger(__METHOD__, [$this->provider]);
        $this->model->load('resources');
        $linked_providers = $this->model->resources->pluck('provider')->toArray();
        $this->providers = array_values(array_diff($this->providers_all, $linked_providers));
        $this->provider = $this->providers[0] ?? '';
    }

    public function render()
    {
        //logger(__METHOD__, [$this->provider]);
        $this->model->load('resources');
        $linked_providers = $this->model->resources->pluck('provider')->toArray();

        $this->providers = array_values(array_diff($this->providers_all, $linked_providers));
        $this->provider = $this->providers[0] ?? '';

        //logger(__METHOD__, [$this->provider]);
        $view = view()->exists('vendor.kraenzle-ritter.livewire.provider-select')
              ? 'vendor.kraenzle-ritter.livewire.provider-select'
              : 'resources-components::provider-select';

        return view($view, [$this->model, 'providers' => $this->providers]);
    }
}
