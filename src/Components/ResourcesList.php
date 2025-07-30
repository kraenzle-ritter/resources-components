<?php

namespace KraenzleRitter\ResourcesComponents\Components;

use Livewire\Component;

class ResourcesList extends Component
{
    /**
     * The model with resources
     *
     * @var mixed
     */
    public $model;

    /**
     * Whether to show delete buttons
     *
     * @var boolean
     */
    public $deleteButton;

    /**
     * Collection of resources
     *
     * @var \Illuminate\Support\Collection
     */
    public $resources;

    /**
     * Event listeners
     *
     * @var array
     */
    protected $listeners = ['resourcesChanged' => 'render'];

    /**
     * Initialize the component
     *
     * @param mixed $model The model with resources
     * @param boolean $deleteButton Whether to show delete buttons
     * @return void
     */
    public function mount($model, $deleteButton = false)
    {
        $this->model = $model;
        $this->deleteButton = $deleteButton;
    }

    /**
     * Remove a resource by ID
     *
     * @param integer $id Resource ID to remove
     * @return void
     */
    public function removeResource($id)
    {
        // Remove resource from model
        $this->model->removeResource($id);

        // Notify other components that resources have changed
        $this->dispatch('resourcesChanged');
    }

    /**
     * Get provider label for display
     *
     * @param string $providerKey Provider key from database
     * @return string Display label
     */
    public function getProviderLabel($providerKey)
    {
        // Try to get label from config
        $label = config("resources-components.providers.{$providerKey}.label");

        if ($label) {
            return $label;
        }

        // Special handling for Wikipedia locales
        if (strpos($providerKey, 'wikipedia-') === 0) {
            $locale = substr($providerKey, 10);
            return 'Wikipedia (' . strtoupper($locale) . ')';
        }

        // Default: capitalize provider key
        return ucfirst($providerKey);
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Ensure resources are loaded
        $this->model->load('resources');
        $this->resources = $this->model->resources->sortBy('provider');

        // Allow view customization through vendor publishing
        $view = view()->exists('vendor.kraenzle-ritter.livewire.resources-list')
              ? 'vendor.kraenzle-ritter.livewire.resources-list'
              : 'resources-components::livewire.resources-list';

        return view($view, [
            'providerLabels' => $this->resources->mapWithKeys(function ($resource) {
                return [$resource->provider => $this->getProviderLabel($resource->provider)];
            })
        ]);
    }
}
