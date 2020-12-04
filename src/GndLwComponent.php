<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class GndLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'GND';

    public $saveMethod = 'updateOrCreateResource';

    public $removeMethod = 'removeResource'; // url

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        $this->search = trim($search) ?: '';

        $this->queryOptions = $params['queryOptions'] ?? ['limit' => 5];
    }

    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => $this->provider,
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
        $client = new Gnd();

        $resources = $client->search($this->search, $this->queryOptions);

        $view = view()->exists('vendor.kraenzle-ritter.livewire.gnd-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.gnd-lw-component'
              : 'resources-components::gnd-lw-component';

        if (!isset($resources->member) or !count($resources->member)) {
            return view($view, [
                'results' => []
            ]);
        }
        // members : array of matches
        foreach ($resources->member as $resource) {
            $date_start = isset($resource->dateOfBirth) ? $resource->dateOfBirth[0] : '';
            $date_end = isset($resource->dateOfDeath) ? $resource->dateOfDeath[0] : '';

            if ($date_start || $date_end) {
                $dateLine = $date_start . ' – ' . $date_end;
            }

            // other resources
            if (isset($resource->sameAs)) {
                foreach ($resource->sameAs as $sameAs) {
                    $name = $sameAs->collection->abbr ?? $sameAs->collection->name ?? '';
                    $name = strpos($sameAs->id, 'isni.org') > 0 ? 'ISNI' : $name;
                    // isni as name
                    if ($name !== 'DNB') {
                        $result['sameAs'][$name] = $sameAs->id;
                    }
                }
            }
            $results[] = $result ?? [];
        }

        return view($view, [
            'results' => $resources->member
        ]);
    }
}
