<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;

class ProviderSelect extends Component
{
    public $model;
    public $providers_all;
    public $providers;
    public $endpoint;
    public ?string $providerKey = null;
    public ?string $componentToRender = null;
    public array $componentParams = [];
    protected $listeners = ['resourcesChanged' => 'hydrate'];

    public function mount($model, array $providers, string $endpoint = null)
    {
        $this->model = $model;
        $this->endpoint = $endpoint;
        $this->providers_all = array_map('strtolower', $providers);
        $this->filterAvailableProviders();
        $this->updateActiveProvider($this->providers[0] ?? null);
    }

    public function setProvider(string $providerKey)
    {
        $this->updateActiveProvider($providerKey);
    }

    private function updateActiveProvider(?string $providerKey)
    {
        if (!$providerKey) {
            $this->providerKey = null;
            $this->componentToRender = null;
            return;
        }

        // Legacy compatibility: 'wikipedia' => 'wikipedia-de'
        if ($providerKey === 'wikipedia') {
            $providerKey = 'wikipedia-de';
        }

        $this->providerKey = $providerKey;

        $apiType = config('resources-components.providers.' . $providerKey . '.api-type');

        if (!$apiType) {
            $this->componentToRender = null;
            return;
        }

        // Derives the component name from the api-type (e.g. 'Gnd' -> 'gnd-lw-component')
        $this->componentToRender = strtolower($apiType) . '-lw-component';
        if ($apiType === 'ManualInput') {
            $this->componentToRender = 'manual-input-lw-component';
        }

        // Prepares the parameters for the child component
        $this->componentParams = [
            'model' => $this->model,
            'search' => $this->model->resource_search ?? $this->model->name,
            'providerKey' => $providerKey, // Important: Passes the specific provider key
        ];

        // Anton components require the additional 'endpoint' parameter
        if ($apiType === 'Anton') {
            $this->componentParams['endpoint'] = $this->endpoint;
        }
    }

    public function hydrate()
    {
        $this->filterAvailableProviders();
        // Ensures the provider does not disappear if it was the last one
        if (!in_array($this->providerKey, $this->providers) && !empty($this->providers)) {
            $this->updateActiveProvider($this->providers[0]);
        } elseif (empty($this->providers) && count($this->providers_all) > count($this->model->resources)) {
            // Fallback if the last provider was linked
            $this->updateActiveProvider(null);
        }
    }

    private function filterAvailableProviders()
    {
        $this->model->load('resources');
        $linked_providers = $this->model->resources->pluck('provider')->toArray();
        $this->providers = array_values(array_diff($this->providers_all, $linked_providers));
    }

    public function render()
    {
        $view = view()->exists('vendor.kraenzle-ritter.livewire.provider-select')
            ? 'vendor.kraenzle-ritter.livewire.provider-select'
            : 'resources-components::livewire.provider-select';

        return view($view);
    }
}
