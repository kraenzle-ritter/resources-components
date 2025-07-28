<?php

namespace KraenzleRitter\ResourcesComponents\Livewire;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class MultiInstanceAntonLwComponent extends AbstractLivewireComponent
{
    public string $selectedInstance = 'default';
    public array $availableInstances = [];
    public bool $searchAllInstances = false;
    public string $endpoint = 'objects';

    protected function getProviderName(): string
    {
        return 'multi-instance-anton';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('multi-instance-anton');
    }

    public function mount($model, $search = null, $instance = 'default', $endpoint = 'objects'): void
    {
        parent::mount($model, $search);
        
        $this->selectedInstance = $instance;
        $this->endpoint = $endpoint;
        
        // Load available instances from provider
        $provider = $this->getProviderClient();
        $this->availableInstances = $provider->getAllInstances();
    }

    protected function processResults($results): array
    {
        if (!is_array($results)) {
            return [];
        }

        // Group results by instance if searching all instances
        if ($this->searchAllInstances) {
            $groupedResults = [];
            foreach ($results as $result) {
                $instance = $result->anton_instance ?? 'unknown';
                $groupedResults[$instance][] = $result;
            }
            return $groupedResults;
        }

        return $results;
    }

    public function performSearch(): array
    {
        try {
            $this->clearError();
            
            if (empty($this->search)) {
                return [];
            }

            $provider = $this->getProviderClient();
            
            if ($this->searchAllInstances) {
                // Search across all available instances
                $results = $provider->searchAllInstances($this->search, [
                    'size' => $this->size
                ], $this->endpoint);
            } else {
                // Search in selected instance only
                $provider->setInstance($this->selectedInstance);
                $results = $provider->search($this->search, [
                    'size' => $this->size
                ], $this->endpoint);
            }

            return $this->processResults($results);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->setError(
                'Network error occurred while searching Anton. Please check your internet connection.',
                $e
            );
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->setError(
                'Could not connect to Anton instance. Please try again later.',
                $e
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 400) {
                $this->setError(
                    'Invalid search request. Please check your search terms.',
                    $e
                );
            } else if ($e->getResponse()->getStatusCode() === 401) {
                $this->setError(
                    'Authentication failed. Please check your API token.',
                    $e
                );
            } else {
                $this->setError(
                    'Anton search request failed. Please try again.',
                    $e
                );
            }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->setError(
                'Anton service is temporarily unavailable. Please try again later.',
                $e
            );
        } catch (\InvalidArgumentException $e) {
            $this->setError(
                'Invalid search parameters provided.',
                $e
            );
        } catch (\Exception $e) {
            $this->setError(
                'An unexpected error occurred during Anton search. Please try again.',
                $e
            );
        }

        return [];
    }

    public function setInstance(string $instanceName): void
    {
        if (isset($this->availableInstances[$instanceName])) {
            $this->selectedInstance = $instanceName;
        }
    }

    public function toggleSearchAllInstances(): void
    {
        $this->searchAllInstances = !$this->searchAllInstances;
    }

    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function getAvailableEndpoints(): array
    {
        return [
            'objects' => 'Objects',
            'persons' => 'Persons',
            'places' => 'Places',
            'organizations' => 'Organizations',
            'events' => 'Events'
        ];
    }

    public function searchInInstance(string $instanceName): array
    {
        try {
            $provider = $this->getProviderClient();
            return $provider->searchInInstance($instanceName, $this->search, [
                'size' => $this->size
            ], $this->endpoint);
        } catch (\Exception $e) {
            $this->setError("Could not search in instance {$instanceName}.", $e);
            return [];
        }
    }

    public function getInstanceStatus(): array
    {
        $status = [];
        foreach ($this->availableInstances as $name => $config) {
            $status[$name] = [
                'name' => $config['name'] ?? $name,
                'enabled' => $config['enabled'] ?? true,
                'has_token' => !empty($config['token']),
                'has_url' => !empty($config['api_url'])
            ];
        }
        return $status;
    }

    public function render()
    {
        $results = $this->performSearch();

        return view('resources-components::livewire.multi-instance-anton-lw-component', [
            'results' => $results,
            'hasError' => $this->hasError,
            'errorMessage' => $this->errorMessage,
            'availableInstances' => $this->availableInstances,
            'selectedInstance' => $this->selectedInstance,
            'searchAllInstances' => $this->searchAllInstances,
            'endpoint' => $this->endpoint,
            'availableEndpoints' => $this->getAvailableEndpoints(),
            'instanceStatus' => $this->getInstanceStatus()
        ]);
    }
}
