<?php

namespace KraenzleRitter\ResourcesComponents;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;

class WikipediaLwComponent extends Component
{
    public $search;

    public $queryOptions;

    public $model;

    public $resourceable_id;

    public $provider = 'Wikipedia';

    public $base_url;

    public $saveMethod = 'updateOrCreateResource'; // Method name for saving resources

    public $removeMethod = 'removeResource'; // Method name for resource removal

    protected $listeners = ['resourcesChanged' => 'render'];

    public function mount ($model, string $search = '', array $params = [])
    {
        $this->model = $model;

        // Determine provider key and locale
        $providerKey = $params['providerKey'] ?? 'wikipedia-de';
        $locale = 'de'; // Default value

        if (!empty($providerKey) && strpos($providerKey, 'wikipedia-') === 0) {
            // Extract locale from provider key (e.g., 'wikipedia-en' => 'en')
            $locale = substr($providerKey, strlen('wikipedia-'));
            \Log::debug('WikipediaLwComponent: Locale from providerKey: ' . $locale);
        } elseif (!empty($params['locale'])) {
            // Fallback to explicit locale parameter
            $locale = $params['locale'];
            \Log::debug('WikipediaLwComponent: Locale from params: ' . $locale);
        } else {
            \Log::debug('WikipediaLwComponent: Using default locale: ' . $locale);
        }

        // Read base_url from configuration
        $apiUrl = config('resources-components.providers.' . $providerKey . '.base_url');
        $this->base_url = str_replace('/w/api.php', '/wiki/', $apiUrl);

        \Log::debug('WikipediaLwComponent: Set base_url: ' . $this->base_url);

        $this->search = trim($search) ?: '';

        // Ensure locale is set in queryOptions
        $this->queryOptions = $params['queryOptions'] ?? [];
        $this->queryOptions['locale'] = $locale;
        $this->queryOptions['limit'] = $this->queryOptions['limit'] ?? 5;

        \Log::debug('WikipediaLwComponent: providerKey: ' . $providerKey);
        \Log::debug('WikipediaLwComponent: QueryOptions: ', $this->queryOptions);
    }

    public function saveResource($provider_id, $url)
    {
        $data = [
            'provider' => $this->provider,
            'provider_id' => $provider_id,
            'url' => str_replace(' ', '_', $url)
        ];
        $resource = $this->model->{$this->saveMethod}($data);
        $this->model->saveMoreResources('wikipedia');
        $this->dispatch('resourcesChanged');
        event(new ResourceSaved($resource, $this->model->id));
    }

    public function removeResource($url)
    {
        \KraenzleRitter\Resources\Resource::where([
            'url' => $url
        ])->delete();
        $this->dispatch('resourcesChanged');
    }

    public function render()
    {
        $client = new Wikipedia();

        // Ensure the correct locale is used
        \Log::debug('WikipediaLwComponent render: base_url = ' . $this->base_url);
        \Log::debug('WikipediaLwComponent render: queryOptions = ', $this->queryOptions);

        // Perform the search
        $resources = $client->search($this->search, $this->queryOptions);

        // Choose the appropriate view
        $view = view()->exists('vendor.kraenzle-ritter.livewire.wikipedia-lw-component')
              ? 'vendor.kraenzle-ritter.livewire.wikipedia-lw-component'
              : 'resources-components::livewire.wikipedia-lw-component';

        return view($view, [
            'results' => $resources
        ]);
    }
}
