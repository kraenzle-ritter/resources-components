<?php

namespace KraenzleRitter\ResourcesComponents\Components;

use Livewire\Component;
use Illuminate\Support\Str;

class ProviderSelect extends Component
{
    /**
     * The model to associate resources with
     *
     * @var mixed
     */
    public $model;

    /**
     * All available providers from config
     *
     * @var array
     */
    public $providers_all;

    /**
     * Currently available providers (not yet linked)
     *
     * @var array
     */
    public $providers;

    /**
     * Endpoint for specific API types like Anton
     *
     * @var string|null
     */
    public $endpoint;

    /**
     * Currently selected provider key
     *
     * @var string|null
     */
    public ?string $providerKey = null;

    /**
     * The component to render based on provider selection
     *
     * @var string|null
     */
    public ?string $componentToRender = null;

    /**
     * Parameters to pass to the rendered component
     *
     * @var array
     */
    public array $componentParams = [];

    /**
     * Event listeners
     *
     * @var array
     */
    protected $listeners = ['resourcesChanged' => 'hydrate'];

    /**
     * Initialize the component
     *
     * @param mixed $model The model to associate resources with
     * @param array $providers List of provider keys to make available
     * @param string|null $endpoint Specific endpoint for API types like Anton
     * @return void
     */
    public function mount($model, array $providers, string $endpoint = null)
    {
        $this->model = $model;
        $this->endpoint = $endpoint;

        // Normalize provider keys to lowercase
        $this->providers_all = array_map('strtolower', $providers);

        // Filter out already linked providers
        $this->filterAvailableProviders();

        // Set the first available provider as active
        $this->updateActiveProvider($this->providers[0] ?? null);
    }

    /**
     * Change the active provider
     *
     * @param string $providerKey The provider key to set active
     * @return void
     */
    public function setProvider(string $providerKey)
    {
        $this->updateActiveProvider($providerKey);
    }

    /**
     * Update the active provider and prepare component parameters
     *
     * @param string|null $providerKey The provider key to set active
     * @return void
     */
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

        // Get the API type from config
        $apiType = config("resources-components.providers.{$providerKey}.api-type");

        if (!$apiType) {
            $this->componentToRender = null;
            return;
        }

        // Derive the component name from the API type
        // e.g., 'Gnd' -> 'gnd-lw-component'
        $this->componentToRender = strtolower($apiType) . '-lw-component';
        if ($apiType === 'ManualInput') {
            $this->componentToRender = 'manual-input-lw-component';
        }

        // Default search query based on model properties
        $search = $this->model->resource_search ?? $this->model->name ?? '';

        // Prepare parameters based on component type
        switch ($apiType) {
            case 'Wikipedia':
                // Wikipedia components expect: mount($model, string $search = '', string $providerKey = 'wikipedia-de')
                $this->componentParams = [
                    'model' => $this->model,
                    'search' => $search,
                    'providerKey' => $providerKey,
                ];
                break;

            case 'Anton':
                // Anton components need an additional endpoint parameter
                $this->componentParams = [
                    'model' => $this->model,
                    'search' => $search,
                    'providerKey' => $providerKey,
                    'endpoint' => $this->endpoint
                ];
                break;

            default:
                // Standard parameters for other components
                $this->componentParams = [
                    'model' => $this->model,
                    'search' => $search,
                    'params' => ['providerKey' => $providerKey]
                ];
                break;
        }
    }

    /**
     * Update component after resource changes
     *
     * @return void
     */
    public function hydrate()
    {
        $this->filterAvailableProviders();

        // Ensure the provider doesn't disappear if it was the last one
        if (!in_array($this->providerKey, $this->providers) && !empty($this->providers)) {
            $this->updateActiveProvider($this->providers[0]);
        } elseif (empty($this->providers) && count($this->providers_all) > count($this->model->resources)) {
            // Fallback if the last provider was linked
            $this->updateActiveProvider(null);
        }
    }

    /**
     * Filter available providers by removing already linked ones
     *
     * @return void
     */
    private function filterAvailableProviders()
    {
        $this->model->load('resources');
        $linked_providers = $this->model->resources->pluck('provider')->toArray();
        $this->providers = array_values(array_diff($this->providers_all, $linked_providers));
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Allow view customization through vendor publishing
        $view = view()->exists('vendor.kraenzle-ritter.livewire.provider-select')
            ? 'vendor.kraenzle-ritter.livewire.provider-select'
            : 'resources-components::livewire.provider-select';

        return view($view);
    }
}
