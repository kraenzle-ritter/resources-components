<?php

namespace KraenzleRitter\ResourcesComponents\Components;

use Livewire\Component;
use KraenzleRitter\ResourcesComponents\Providers\WikipediaProvider;
use KraenzleRitter\ResourcesComponents\Events\ResourceSaved;
use KraenzleRitter\ResourcesComponents\Traits\ProviderComponentTrait;

class WikipediaLivewireComponent extends Component
{
    use ProviderComponentTrait;

    /**
     * Search query string
     *
     * @var string
     */
    public $search;

    /**
     * The model to associate resources with
     *
     * @var mixed
     */
    public $model;

    /**
     * Provider key identifying this component
     *
     * @var string
     */
    public $providerKey = 'wikipedia-de';

    /**
     * Provider label for display
     *
     * @var string
     */
    public $providerLabel = 'Wikipedia (DE)';

    /**
     * Search results
     *
     * @var array
     */
    public $results = [];

    /**
     * Error message
     *
     * @var string|null
     */
    public $error = null;

    /**
     * Provider instance
     *
     * @var WikipediaProvider
     */
    protected $provider;

    /**
     * Event listeners
     *
     * @var array
     */
    protected $listeners = ['resourcesChanged' => 'render'];

    /**
     * Initialize the component
     *
     * @param mixed $model The model to associate resources with
     * @param string $search Initial search query
     * @param string $providerKey The provider key (e.g. 'wikipedia-de', 'wikipedia-en')
     * @return void
     */
    public function mount($model, string $search = '', string $providerKey = 'wikipedia-de')
    {
        $this->model = $model;
        $this->search = $search;
        $this->providerKey = $providerKey;

        // Set provider label based on language code
        $locale = explode('-', $providerKey)[1] ?? 'de';
        $this->providerLabel = 'Wikipedia (' . strtoupper($locale) . ')';

        // Initialize provider
        $config = config('resources-components.providers.' . $providerKey) ?? [
            'base_url' => "https://{$locale}.wikipedia.org/w/api.php",
            'label' => $this->providerLabel
        ];

        $this->provider = new WikipediaProvider($providerKey, $config);
    }

    /**
     * Perform search with current query
     *
     * @return void
     */
    public function search()
    {
        if (empty($this->search)) {
            $this->results = [];
            $this->error = null;
            return;
        }

        $params = [
            'limit' => config('resources-components.limit', 5)
        ];

        $rawResults = $this->provider->search($this->search, $params);

        if ($rawResults === null) {
            $this->error = 'Error connecting to Wikipedia. Please try again.';
            $this->results = [];
            return;
        }

        $this->results = $this->provider->processResult($rawResults);
        $this->error = null;
    }

    /**
     * Save a resource from search results
     *
     * @param string $title The resource title
     * @param string $url The resource URL
     * @param string|null $rawData JSON string of raw data
     * @return void
     */
    public function saveResource(string $title, string $url, ?string $rawData = null)
    {
        try {
            $resourceData = [
                'provider' => $this->providerKey,
                'title' => $title,
                'url' => $url
            ];

            if ($rawData) {
                $resourceData['data'] = $rawData;
            }

            // Save the resource using the trait method
            $resource = $this->saveResourceToModel($this->model, $resourceData);

            // Reset search after saving
            $this->search = '';
            $this->results = [];

            // Dispatch event
            event(new ResourceSaved($resource, $this->model->id));

            // Flash success message
            session()->flash('message', 'Resource successfully saved.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error saving resource: ' . $e->getMessage());
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('resources-components::livewire.wikipedia', [
            'model' => $this->model,
            'results' => $this->results,
            'error' => $this->error,
            'providerKey' => $this->providerKey,
            'providerLabel' => $this->providerLabel
        ]);
    }
}
