<?php

namespace KraenzleRitter\ResourcesComponents\Abstracts;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

abstract class AbstractLivewireComponent extends Component
{
    public $search = '';
    public $queryOptions = [];
    public $model;
    public $resourceable_id;
    public $provider;
    public $saveMethod = 'updateOrCreateResource';
    public $removeMethod = 'removeResource';

    protected $listeners = ['resourcesChanged' => 'render'];

    /**
     * Mount the component
     *
     * @param mixed $model
     * @param string $search
     * @param array $params
     */
    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;
        $this->search = trim($search);
        $this->queryOptions = $params['queryOptions'] ?? $this->getDefaultOptions();
        $this->provider = $this->getProviderName();
    }

    /**
     * Save a resource
     *
     * @param string $provider_id
     * @param string $url
     * @param string|null $full_json
     */
    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => $url,
            'full_json' => $this->processFullJson($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);

        if (method_exists($this->model, 'saveMoreResources')) {
            $this->model->saveMoreResources(strtolower($this->provider));
        }

        $this->dispatch('resourcesChanged');
        event(new ResourceSaved($resource, $this->model->id));
    }

    /**
     * Remove a resource
     *
     * @param string $url
     */
    public function removeResource($url)
    {
        Resource::where(['url' => $url])->delete();
        $this->dispatch('resourcesChanged');
    }

    /**
     * Process the full JSON data
     *
     * @param string|null $full_json
     * @return mixed
     */
    protected function processFullJson($full_json)
    {
        if ($full_json === null) {
            return null;
        }

        if (is_string($full_json)) {
            $decoded = json_decode($full_json);
            return $decoded ?: $full_json;
        }

        return $full_json;
    }

    /**
     * Get the view name for this component
     *
     * @return string
     */
    protected function getViewName(): string
    {
        $providerName = strtolower($this->getProviderName());
        $componentName = $providerName . '-lw-component';

        $customView = "vendor.kraenzle-ritter.livewire.{$componentName}";
        $defaultView = "resources-components::{$componentName}";

        return view()->exists($customView) ? $customView : $defaultView;
    }

    /**
     * Get default options for this provider
     *
     * @return array
     */
    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    /**
     * Get the provider name
     *
     * @return string
     */
    abstract protected function getProviderName(): string;

    /**
     * Get the provider client instance
     *
     * @return mixed
     */
    abstract protected function getProviderClient();

    /**
     * Process the search results before passing to view
     *
     * @param mixed $results
     * @return mixed
     */
    abstract protected function processResults($results);
}
