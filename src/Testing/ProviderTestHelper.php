<?php

namespace KraenzleRitter\ResourcesComponents\Testing;

use KraenzleRitter\ResourcesComponents\Anton;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Geonames;
use KraenzleRitter\ResourcesComponents\Metagrid;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Ortsnamen;

/**
 * Helper class for testing providers without the command overhead
 */
class ProviderTestHelper
{
    /**
     * Test Wikipedia provider
     */
    public static function testWikipediaProvider($locale = 'de', $searchTerm = 'Albert Einstein')
    {
        $client = new Wikipedia();
        $providerKey = "wikipedia-{$locale}";
        
        $queryOptions = [
            'providerKey' => $providerKey,
            'locale' => $locale,
            'limit' => 1
        ];

        $results = $client->search($searchTerm, $queryOptions);
        
        if (!empty($results)) {
            $firstResult = $results[0];
            return [
                'success' => true,
                'title' => $firstResult->title ?? 'Unknown',
                'provider_id' => $firstResult->pageid ?? '',
                'provider' => strtolower($providerKey),
                'data' => $firstResult,
                'description' => isset($firstResult->snippet) ? strip_tags($firstResult->snippet) : ''
            ];
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test Wikidata provider
     */
    public static function testWikidataProvider($searchTerm = 'Albert Einstein')
    {
        $client = new Wikidata();
        $results = $client->search($searchTerm, ['limit' => 1]);

        if (!empty($results)) {
            $firstResult = $results[0] ?? null;
            if ($firstResult) {
                return [
                    'success' => true,
                    'title' => $firstResult->label ?? 'Unknown',
                    'provider_id' => $firstResult->id ?? '',
                    'provider' => 'wikidata',
                    'data' => $firstResult
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test GND provider
     */
    public static function testGndProvider($searchTerm = 'Albert Einstein')
    {
        $client = new Gnd();
        $results = $client->search($searchTerm, ['limit' => 1]);

        if (!empty($results->member)) {
            $firstResult = $results->member[0] ?? null;
            if ($firstResult) {
                $id = substr($firstResult->id, strrpos($firstResult->id, '/') + 1);
                return [
                    'success' => true,
                    'title' => $firstResult->preferredName ?? 'Unknown',
                    'provider_id' => $id,
                    'provider' => 'gnd',
                    'data' => $firstResult
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test Geonames provider
     */
    public static function testGeonamesProvider($searchTerm = 'Zürich')
    {
        // Check if Geonames username is set
        $userName = config("resources-components.providers.geonames.user_name");
        if (empty($userName) || $userName === 'demo') {
            return ['success' => false, 'error' => 'No valid username found'];
        }

        $client = new Geonames();
        $results = $client->search($searchTerm, ['limit' => 1]);

        if (!empty($results)) {
            $firstResult = $results[0] ?? null;
            if ($firstResult) {
                // Build description from available Geonames fields
                $descParts = [];
                if (isset($firstResult->adminName1)) $descParts[] = $firstResult->adminName1;
                if (isset($firstResult->countryName)) $descParts[] = $firstResult->countryName;
                if (isset($firstResult->fclName)) $descParts[] = $firstResult->fclName;
                $description = implode(', ', $descParts);

                return [
                    'success' => true,
                    'title' => $firstResult->name ?? 'Unknown',
                    'provider_id' => $firstResult->geonameId ?? '',
                    'provider' => 'geonames',
                    'data' => $firstResult,
                    'description' => $description ?: ''
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test Metagrid provider
     */
    public static function testMetagridProvider($searchTerm = 'Albert Einstein')
    {
        $client = new Metagrid();
        $results = $client->search($searchTerm, ['limit' => 1]);

        if (!empty($results)) {
            $firstResult = $results[0] ?? null;
            if ($firstResult) {
                return [
                    'success' => true,
                    'title' => $firstResult->name ?? 'Unknown',
                    'provider_id' => $firstResult->id ?? '',
                    'provider' => 'metagrid',
                    'data' => $firstResult
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test Idiotikon provider
     */
    public static function testIdiotikonProvider($searchTerm = 'Allmend')
    {
        $client = new Idiotikon();
        $results = $client->search($searchTerm, ['limit' => 1]);

        if (!empty($results)) {
            $firstResult = $results[0] ?? null;
            if ($firstResult) {
                // Try to extract ID from different possible properties
                $id = $firstResult->id ?? $firstResult->lemmaId ?? $firstResult->lemma_id ?? $firstResult->lemmaID ?? '';
                $title = $firstResult->lemma ?? $firstResult->lemmaText ?? $firstResult->title ?? $firstResult->name ?? 'Unknown';
                
                return [
                    'success' => true,
                    'title' => $title,
                    'provider_id' => $id,
                    'provider' => 'idiotikon',
                    'data' => $firstResult
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test Ortsnamen provider
     */
    public static function testOrtsnamenProvider($searchTerm = 'Zürich')
    {
        $client = new Ortsnamen();
        $results = $client->search($searchTerm, ['limit' => 1]);

        if (!empty($results)) {
            $firstResult = $results[0] ?? null;
            if ($firstResult) {
                return [
                    'success' => true,
                    'title' => $firstResult->name ?? 'Unknown',
                    'provider_id' => $firstResult->id ?? '',
                    'provider' => 'ortsnamen',
                    'data' => $firstResult
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Test Anton provider
     */
    public static function testAntonProvider($providerKey, $searchTerm = 'archiv', $endpoint = 'actors')
    {
        $client = new Anton($providerKey);
        $results = $client->search($searchTerm, ['limit' => 1], $endpoint);

        if (!empty($results)) {
            $firstResult = $results[0] ?? null;
            if ($firstResult) {
                return [
                    'success' => true,
                    'title' => $firstResult->fullname ?? $firstResult->title ?? 'Unknown',
                    'provider_id' => $firstResult->id ?? '',
                    'provider' => $providerKey,
                    'data' => $firstResult
                ];
            }
        }
        
        return ['success' => false, 'error' => 'No results found'];
    }

    /**
     * Get URL for provider
     */
    public static function getProviderUrl($provider, $providerId)
    {
        $targetUrlTemplate = config("resources-components.providers.{$provider}.target_url");
        
        if ($targetUrlTemplate) {
            return str_replace('{provider_id}', $providerId, $targetUrlTemplate);
        }
        
        return null;
    }
}
