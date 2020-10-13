<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Wikipedia;

class WikipediaLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'Wikipedia';

    public $base_url;

    public $saveMethod = 'updateOrCreateResource'; // id, resource

    public $removeMethod = 'removeResource'; // url

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount ($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $locale = $params['locale'] ?? 'de';

        $this->base_url = $base_uri = 'https://'.$locale.'.wikipedia.org/wiki/';

        $this->search = trim($search) ?: '';

        $this->queryOptions = $params['queryOptions'] ?? ['locale' => $locale, 'limit' => 5];
    }

    public function saveResource($provider_id, $url)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => $url
        ];
        $this->model->{$this->saveMethod}($data);
        $this->emit('resourcesChanged');
    }

    public function removeResource($url)
    {
        \KraenzleRitter\Resources\Resource::where([
            'url' => $url
        ])->delete();
        $this->emit('resourcesChanged');
    }

    public function render()
    {
        $client = new Wikipedia();

        $resources = $client->search($this->search, $this->queryOptions);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.wikipedia-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.wikipedia-lw-component'
              : 'resources-components::wikipedia-lw-component';

        return view($view, [
            'results' => $resources
        ]);

    }
}
