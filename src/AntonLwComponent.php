<?php

namespace KraenzleRitter\ResourcesComponents;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class AntonLwComponent extends AbstractLivewireComponent
{
    public $endpoint = 'objects';

    protected function getProviderName(): string
    {
        return 'Anton';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('anton');
    }

    protected function getDefaultOptions(): array
    {
        return ['limit' => 5];
    }

    protected function processResults($results)
    {
        return $results;
    }

    public function mount($model, string $search = '', array $params = [], string $endpoint = 'objects')
    {
        $this->endpoint = $endpoint;

        // Set default search if empty
        if (empty(trim($search))) {
            $search = 'Cassirer';
        }

        parent::mount($model, $search, $params);
    }

    /**
     * Perform search with error handling (override for endpoint support)
     *
     * @return array
     */
    protected function performSearch(): array
    {
        if (!$this->search) {
            return [];
        }

        try {
            $this->clearError();
            $client = $this->getProviderClient();
            $resources = $client->search($this->search, $this->queryOptions, $this->endpoint);
            return $this->processResults($resources);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->setError(
                "Network error while searching '{$this->search}'. Please check your internet connection and try again.",
                $e
            );
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->setError(
                "Cannot connect to {$this->getProviderName()} service. The service might be temporarily unavailable.",
                $e
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 400) {
                $this->setError(
                    "Invalid search query '{$this->search}'. Please try a different search term.",
                    $e
                );
            } else {
                $this->setError(
                    "Error from {$this->getProviderName()} service. Please try again later.",
                    $e
                );
            }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->setError(
                "{$this->getProviderName()} service is temporarily unavailable. Please try again later.",
                $e
            );
        } catch (\InvalidArgumentException $e) {
            $this->setError(
                "Invalid search parameters for '{$this->search}'. Please check your input.",
                $e
            );
        } catch (\Exception $e) {
            $this->setError(
                "An unexpected error occurred while searching '{$this->search}'. Please try again.",
                $e
            );
        }

        return [];
    }    public function saveResource($provider_id, $url, $full_json = null)
    {
        $data = [
            'provider' => config('resources-components.anton.provider-slug'),
            'provider_id' => $provider_id,
            'url' => config('resources-components.anton.url'). '/' . $this->endpoint . '/' . $provider_id,
            'full_json' => $this->processFullJson($full_json)
        ];

        $resource = $this->model->{$this->saveMethod}($data);
        $this->dispatch('resourcesChanged');
        event(new \KraenzleRitter\ResourcesComponents\Events\ResourceSaved($resource, $this->model->id));
    }

    public function render()
    {
        $results = $this->performSearch();

        return view($this->getViewName(), [
            'results' => $results,
            'hasError' => $this->hasError,
            'errorMessage' => $this->errorMessage
        ]);
    }
}
