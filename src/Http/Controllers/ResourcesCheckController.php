<?php

namespace KraenzleRitter\ResourcesComponents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Geonames;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Metagrid;
use KraenzleRitter\ResourcesComponents\Ortsnamen;
use KraenzleRitter\ResourcesComponents\Anton;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ResourcesCheckController
{
    /**
     * Get test query for a provider from configuration
     * Falls back to a default if not configured
     */
    protected function getTestQuery($providerKey)
    {
        $providers = Config::get('resources-components.providers');
        return $providers[$providerKey]['test_search'] ?? 'test';
    }

    /**
     * Zeigt die Übersichtsseite mit Status aller Provider
     */
    public function index()
    {
        $providers = Config::get('resources-components.providers');
        $results = [];

        // Schleife durch alle Provider und teste sie
        foreach ($providers as $key => $provider) {
            $results[$key] = $this->checkProvider($key, $provider);
        }

        // Check the database table
        $dbStatus = [
            'exists' => Schema::hasTable('resources'),
            'message' => Schema::hasTable('resources') ? 
                'Resources-Tabelle ist vorhanden' : 
                'Resources-Tabelle fehlt - bitte Migration ausführen'
        ];

        // Complete configuration for display
        $configPath = config_path('resources-components.php');
        $fullConfig = file_exists($configPath) ? include($configPath) : Config::get('resources-components');

        return view('resources-components::check.index', [
            'results' => $results,
            'dbStatus' => $dbStatus,
            'fullConfig' => $fullConfig
        ]);
    }

    /**
     * Zeigt Details zu einem spezifischen Provider
     */
    public function showProvider($provider, Request $request)
    {
        $providers = Config::get('resources-components.providers');
        
        if (!isset($providers[$provider])) {
            return redirect()->route('resources.check.index')
                ->with('error', "Provider {$provider} ist nicht konfiguriert.");
        }

        // Redirect ManualInput to index with info message
        if (($providers[$provider]['api-type'] ?? '') === 'ManualInput') {
            return redirect()->route('resources.check.index')
                ->with('info', "Manual Input Provider benötigt keine API-Tests.");
        }

        $searchTerm = $request->get('search', $this->getTestQuery($provider));
        $showAll = $request->has('show_all');
        $endpoint = $request->get('endpoint', 'actors'); // Default endpoint for Anton providers
        
        // Wenn 'show_all' aktiviert ist, heben wir das Limit auf oder setzen es hoch
        $result = $this->testProviderWithSearch($provider, $providers[$provider], $searchTerm, $showAll, $endpoint);

        // Available endpoints for Anton providers
        $availableEndpoints = [];
        if (($providers[$provider]['api-type'] ?? '') === 'Anton') {
            $availableEndpoints = ['actors', 'places', 'keywords', 'objects'];
        }

        return view('resources-components::check.provider', [
            'provider' => $provider,
            'config' => $providers[$provider],
            'result' => $result,
            'searchTerm' => $searchTerm,
            'showAll' => $showAll,
            'endpoint' => $endpoint,
            'availableEndpoints' => $availableEndpoints
        ]);
    }

    /**
     * Führt einen Test für einen Provider aus
     *
     * @param string $key Provider-Key
     * @param array $config Provider configuration
     * @param bool $includeResults Ob Ergebnisse zurückgegeben werden sollen
     * @param bool $ignoreLimit Ob das Limit ignoriert werden soll (alle Ergebnisse anzeigen)
     * @return array
     */
    protected function checkProvider($key, $config, $includeResults = false, $ignoreLimit = false)
    {
        $searchTerm = $this->getTestQuery($key);
        $status = 'error';
        $message = 'Nicht getestet';
        $apiResults = [];
        
        // Determine the limit from provider configuration, global limit or set default
        $limit = $ignoreLimit ? 50 : ($config['limit'] ?? config('resources-components.limit') ?? 5);

        try {
            switch ($config['api-type'] ?? '') {
                case 'Wikidata':
                    $client = new Wikidata();
                    $results = $client->search($searchTerm, ['locale' => 'de', 'limit' => $limit]);
                    break;
                    
                case 'Wikipedia':
                    $client = new Wikipedia();
                    $results = $client->search($searchTerm, ['locale' => explode('-', $key)[1] ?? 'de', 'limit' => $limit]);
                    break;
                    
                case 'Gnd':
                    $client = new Gnd();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Geonames':
                    $client = new Geonames();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Idiotikon':
                    $client = new Idiotikon();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Metagrid':
                    $client = new Metagrid();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Ortsnamen':
                    $client = new Ortsnamen();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Anton':
                    // Anton braucht einen spezifischen Provider-Key
                    $client = new Anton($key);
                    $results = $client->search($searchTerm, ['limit' => $limit], 'actors'); // Default endpoint for overview
                    break;
                    
                case 'ManualInput':
                    // Manual Input braucht keinen API-Test
                    $status = 'success';
                    $message = 'Manual Input - kein API-Test erforderlich';
                    $results = [];
                    break;
                    
                default:
                    $status = 'warning';
                    $message = 'Unbekannter Provider-Typ: ' . ($config['api-type'] ?? 'nicht definiert');
                    $results = [];
            }

            // Check the results (but skip for ManualInput which already has status and message set)
            if (($config['api-type'] ?? '') !== 'ManualInput') {
                if (!empty($results)) {
                    $status = 'success';
                    
                    // Handle arrays and objects correctly for count()
                    if (is_array($results) || $results instanceof \Countable) {
                        $resultsCount = count($results);
                        $message = $resultsCount . ' Ergebnisse gefunden';
                    } else if (is_object($results)) {
                        // Convert object to array if possible
                        $resultsArray = json_decode(json_encode($results), true);
                        $resultsCount = is_array($resultsArray) ? count($resultsArray) : 1;
                        $message = $resultsCount . ' Ergebnisse gefunden';
                        // Ensure that $results is correctly formatted for display
                        $results = $resultsArray;
                    } else {
                        $message = 'Ergebnisse gefunden';
                    }
                    
                    $apiResults = $includeResults ? $results : [];
                } else {
                    $status = 'warning';
                    $message = 'Keine Ergebnisse gefunden';
                }
            } else {
                // For ManualInput, don't include any results
                $apiResults = [];
            }
        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Error: ' . $e->getMessage();
            Log::error("Provider check error for {$key}: " . $e->getMessage());
        }

        return [
            'status' => $status,
            'message' => $message,
            'results' => $apiResults
        ];
    }
    
    /**
     * Führt einen Test für einen Provider mit einem spezifischen Suchterm aus
     */
    protected function testProviderWithSearch($key, $config, $searchTerm, $ignoreLimit = false, $endpoint = 'actors')
    {
        $status = 'error';
        $message = 'Nicht getestet';
        $apiResults = [];
        
        // Determine the limit from provider configuration, global limit or set default
        $limit = $ignoreLimit ? 50 : ($config['limit'] ?? config('resources-components.limit') ?? 5);

        try {
            switch ($config['api-type'] ?? '') {
                case 'Wikidata':
                    $client = new Wikidata();
                    $results = $client->search($searchTerm, ['locale' => 'de', 'limit' => $limit]);
                    break;
                    
                case 'Wikipedia':
                    $client = new Wikipedia();
                    $results = $client->search($searchTerm, ['locale' => explode('-', $key)[1] ?? 'de', 'limit' => $limit]);
                    break;
                    
                case 'Gnd':
                    $client = new Gnd();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Geonames':
                    $client = new Geonames();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Idiotikon':
                    $client = new Idiotikon();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Metagrid':
                    $client = new Metagrid();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Ortsnamen':
                    $client = new Ortsnamen();
                    $results = $client->search($searchTerm, ['limit' => $limit]);
                    break;
                    
                case 'Anton':
                    $client = new Anton($key);
                    $results = $client->search($searchTerm, ['limit' => $limit], $endpoint);
                    break;
                    
                case 'ManualInput':
                    // Manual Input Provider has no API to test
                    $status = 'success';
                    $message = 'Manual Input - kein API-Test erforderlich';
                    $apiResults = [];
                    return [
                        'status' => $status,
                        'message' => $message,
                        'results' => $apiResults
                    ];
                    
                default:
                    throw new \Exception("Unbekannter API-Typ: " . ($config['api-type'] ?? 'nicht gesetzt'));
            }

            if (is_array($results) && count($results) > 0) {
                $status = 'success';
                $message = count($results) . ' Ergebnisse gefunden';
                $apiResults = $results;
            } elseif (is_object($results)) {
                $status = 'success';
                $message = 'Ergebnisse gefunden (Objekt)';
                $apiResults = $results;
            } else {
                $status = 'warning';
                $message = 'Keine Ergebnisse gefunden';
            }

        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Error: ' . $e->getMessage();
            Log::error("Provider {$key} test error: " . $e->getMessage());
        }

        return [
            'status' => $status,
            'message' => $message,
            'results' => $apiResults
        ];
    }
    
    /**
     * Shows the complete configuration
     */
    public function showConfig()
    {
        // Tries to load the published configuration
        $configPath = config_path('resources-components.php');
        $fullConfig = file_exists($configPath) ? include($configPath) : null;
        
        // If no published configuration exists, load the package configuration
        if (!$fullConfig) {
            $fullConfig = Config::get('resources-components');
        }

        return view('resources-components::check.config', [
            'config' => $fullConfig
        ]);
    }
}
