<?php

namespace KraenzleRitter\ResourcesComponents\Livewire;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractLivewireComponent;
use KraenzleRitter\ResourcesComponents\Factories\ProviderFactory;

class MultiLanguageWikipediaLwComponent extends AbstractLivewireComponent
{
    public array $selectedLanguages = ['de'];
    public array $availableLanguages = [];
    public bool $searchAllLanguages = false;
    public int $limitPerLanguage = 3;

    protected function getProviderName(): string
    {
        return 'multilanguage-wikipedia';
    }

    protected function getProviderClient()
    {
        return ProviderFactory::create('multilanguage-wikipedia');
    }

    public function mount($model, $search = null, $languages = ['de']): void
    {
        parent::mount($model, $search);
        
        $this->selectedLanguages = is_array($languages) ? $languages : [$languages];
        
        // Load available languages from provider
        $provider = $this->getProviderClient();
        $this->availableLanguages = $provider->getSupportedLanguages();
    }

    protected function processResults($results): array
    {
        if (!is_array($results)) {
            return [];
        }

        // Group results by language for better display
        $groupedResults = [];
        foreach ($results as $result) {
            $language = $result->language ?? 'unknown';
            $groupedResults[$language][] = $result;
        }

        return $groupedResults;
    }

    public function performSearch(): array
    {
        try {
            $this->clearError();
            
            if (empty($this->search)) {
                return [];
            }

            $provider = $this->getProviderClient();
            
            if ($this->searchAllLanguages) {
                // Search across all supported languages
                $results = $provider->searchAllLanguages($this->search, $this->limitPerLanguage);
            } else {
                // Search in selected languages only
                $results = $provider->search($this->search, [
                    'languages' => $this->selectedLanguages,
                    'limit' => $this->size
                ]);
            }

            return $this->processResults($results);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->setError(
                'Network error occurred while searching Wikipedia. Please check your internet connection.',
                $e
            );
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->setError(
                'Could not connect to Wikipedia. Please try again later.',
                $e
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 400) {
                $this->setError(
                    'Invalid search request. Please check your search terms.',
                    $e
                );
            } else {
                $this->setError(
                    'Wikipedia search request failed. Please try again.',
                    $e
                );
            }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->setError(
                'Wikipedia service is temporarily unavailable. Please try again later.',
                $e
            );
        } catch (\InvalidArgumentException $e) {
            $this->setError(
                'Invalid search parameters provided.',
                $e
            );
        } catch (\Exception $e) {
            $this->setError(
                'An unexpected error occurred during Wikipedia search. Please try again.',
                $e
            );
        }

        return [];
    }

    public function toggleLanguage(string $languageCode): void
    {
        if (in_array($languageCode, $this->selectedLanguages)) {
            $this->selectedLanguages = array_values(array_diff($this->selectedLanguages, [$languageCode]));
        } else {
            $this->selectedLanguages[] = $languageCode;
        }
        
        // Ensure at least one language is selected
        if (empty($this->selectedLanguages)) {
            $this->selectedLanguages = ['de'];
        }
    }

    public function setLanguages(array $languages): void
    {
        $this->selectedLanguages = $languages;
    }

    public function toggleSearchAllLanguages(): void
    {
        $this->searchAllLanguages = !$this->searchAllLanguages;
    }

    public function getArticle(string $title, string $language): ?object
    {
        try {
            $provider = $this->getProviderClient();
            return $provider->getArticle($title, $language);
        } catch (\Exception $e) {
            $this->setError('Could not retrieve article details.', $e);
            return null;
        }
    }

    public function render()
    {
        $results = $this->performSearch();

        return view('resources-components::livewire.multi-language-wikipedia-lw-component', [
            'results' => $results,
            'hasError' => $this->hasError,
            'errorMessage' => $this->errorMessage,
            'availableLanguages' => $this->availableLanguages,
            'selectedLanguages' => $this->selectedLanguages,
            'searchAllLanguages' => $this->searchAllLanguages
        ]);
    }
}
