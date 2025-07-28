<?php

namespace KraenzleRitter\ResourcesComponents\Providers;

use KraenzleRitter\ResourcesComponents\Abstracts\AbstractProvider;

/**
 * Multi-Language Wikipedia Provider
 * 
 * Supports searching across different Wikipedia language versions
 * Configurable language codes (de, en, fr, it, es, etc.)
 */
class MultiLanguageWikipediaProvider extends AbstractProvider
{
    protected array $supportedLanguages = [
        'de' => 'Deutsch',
        'en' => 'English', 
        'fr' => 'Français',
        'it' => 'Italiano',
        'es' => 'Español',
        'pt' => 'Português',
        'nl' => 'Nederlands',
        'sv' => 'Svenska',
        'da' => 'Dansk',
        'no' => 'Norsk',
        'fi' => 'Suomi',
        'pl' => 'Polski',
        'ru' => 'Русский',
        'ja' => '日本語',
        'zh' => '中文',
        'ko' => '한국어',
        'ar' => 'العربية',
        'he' => 'עברית',
        'hi' => 'हिन्दी'
    ];

    protected string $defaultLanguage = 'de';

    public function getBaseUrl(): string
    {
        $language = $this->getConfigValue('language', $this->defaultLanguage);
        return "https://{$language}.wikipedia.org/w/api.php";
    }

    public function getProviderName(): string
    {
        return 'MultiLanguageWikipedia';
    }

    protected function setDefaultParams(): void
    {
        parent::setDefaultParams();
        $this->defaultParams['language'] = $this->getConfigValue('language', $this->defaultLanguage);
    }

    /**
     * Search across one or multiple Wikipedia language versions
     *
     * @param string $search Search term
     * @param array $params Parameters including 'languages' array for multi-language search
     * @return array Search results with language information
     */
    public function search(string $search, array $params = []): array
    {
        $search = $this->sanitizeSearch($search);
        
        // Return empty array for empty search terms
        if (empty(trim($search))) {
            return [];
        }
        
        $params = $this->mergeParams($params);

        $languages = $params['languages'] ?? [$params['language'] ?? $this->defaultLanguage];
        $limit = $params['limit'] ?? 5;
        $allResults = [];

        foreach ($languages as $language) {
            if (!$this->isLanguageSupported($language)) {
                continue;
            }

            $results = $this->searchInLanguage($search, $language, $limit);
            
            foreach ($results as $result) {
                $result->language = $language;
                $result->language_name = $this->supportedLanguages[$language];
                $result->url = "https://{$language}.wikipedia.org/wiki/" . str_replace(' ', '_', $result->title);
                $allResults[] = $result;
            }
        }

        return $allResults;
    }

    /**
     * Search in a specific Wikipedia language version
     */
    protected function searchInLanguage(string $search, string $language, int $limit): array
    {
        // Create language-specific client
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => "https://{$language}.wikipedia.org/w/api.php"
        ]);

        $searchString = trim(str_replace(' ', '_', $search), '_');

        $queryParams = [
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => 'intitle:' . $searchString,
            'srnamespace' => '0',
            'srlimit' => $limit
        ];

        $queryString = '?' . http_build_query($queryParams);
        $result = $this->makeRequest('GET', $queryString);

        if ($result && isset($result->query->searchinfo->totalhits) && $result->query->searchinfo->totalhits > 0) {
            return $result->query->search;
        }

        return [];
    }

    /**
     * Get article extract in specific language
     */
    public function getArticle(string $title, string $language = null): ?object
    {
        $language = $language ?? $this->defaultLanguage;
        
        if (!$this->isLanguageSupported($language)) {
            return null;
        }

        // Update client for specific language
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => "https://{$language}.wikipedia.org/w/api.php"
        ]);

        $title = trim(str_replace(' ', '_', $title), '_');

        $queryParams = [
            'action' => 'query',
            'titles' => $title,
            'format' => 'json',
            'prop' => 'extracts',
            'exintro' => '',
            'explaintext' => '',
            'redirects' => '1',
            'indexpageids' => ''
        ];

        $queryString = '?' . http_build_query($queryParams);
        $result = $this->makeRequest('GET', $queryString);

        if ($result && isset($result->query->pages)) {
            foreach ($result->query->pages as $article) {
                $article->language = $language;
                $article->language_name = $this->supportedLanguages[$language];
                return $article;
            }
        }

        return null;
    }

    /**
     * Get available language codes
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $language): bool
    {
        return array_key_exists($language, $this->supportedLanguages);
    }

    /**
     * Search across all supported languages
     */
    public function searchAllLanguages(string $search, int $limitPerLanguage = 2): array
    {
        return $this->search($search, [
            'languages' => array_keys($this->supportedLanguages),
            'limit' => $limitPerLanguage
        ]);
    }
}
