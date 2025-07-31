<?php

namespace KraenzleRitter\ResourcesComponents\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use KraenzleRitter\Resources\Resource;
use KraenzleRitter\ResourcesComponents\Anton;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Geonames;
use KraenzleRitter\ResourcesComponents\Metagrid;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Ortsnamen;

class TestResourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources-components:test-resources
                            {--provider= : Test a specific provider}
                            {--list : List all available providers}
                            {--no-cleanup : Do not cleanup test resources after running tests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test creating resources for different providers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // If --list option is provided, just list all available providers and exit
        if ($this->option('list')) {
            $this->listAvailableProviders();
            return Command::SUCCESS;
        }
        
        $this->info('Testing resources creation for different providers');
        
        // Create a test model that implements HasResources
        $testModel = $this->createTestModel();
        
        // Array to collect all resources
        $resources = collect();
        
        // Check if a specific provider is requested
        $specificProvider = $this->option('provider');
        
        if ($specificProvider) {
            $this->info("Testing only provider: {$specificProvider}");
            $this->testSpecificProvider($testModel, $resources, $specificProvider);
        } else {
            // Test each provider
            $this->testWikipediaProviders($testModel, $resources);
            $this->testWikidataProvider($testModel, $resources);
            $this->testGndProvider($testModel, $resources);
            $this->testGeonamesProvider($testModel, $resources);
            $this->testMetagridProvider($testModel, $resources);
            $this->testIdiotikonProvider($testModel, $resources);
            $this->testOrtsnamenProvider($testModel, $resources);
            $this->testAntonProviders($testModel, $resources);
        }
        
        // Display results
        $this->displayResults($resources);
        
        // Clean up if requested
        if (!$this->option('no-cleanup') && $resources->count() > 0) {
            $this->cleanup($testModel, $resources);
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * List all available providers
     */
    protected function listAvailableProviders()
    {
        $this->info("Available providers:");
        
        $providers = [
            'Wikipedia' => ['wikipedia-de', 'wikipedia-en', 'wikipedia-fr', 'wikipedia-it'],
            'Other' => ['wikidata', 'gnd', 'geonames', 'metagrid', 'idiotikon', 'ortsnamen'],
            'Anton API' => ['georgfischer', 'kba', 'gosteli']
        ];
        
        foreach ($providers as $group => $providerList) {
            $this->line("\n<fg=yellow>{$group}:</>");
            foreach ($providerList as $provider) {
                $this->line("  - {$provider}");
            }
        }
    }
    
    /**
     * Test a specific provider
     */
    protected function testSpecificProvider($testModel, Collection $resources, $provider)
    {
        switch ($provider) {
            case 'wikipedia-de':
            case 'wikipedia-en':
            case 'wikipedia-fr':
            case 'wikipedia-it':
                $this->testWikipediaProviders($testModel, $resources, [$provider]);
                break;
            case 'wikidata':
                $this->testWikidataProvider($testModel, $resources);
                break;
            case 'gnd':
                $this->testGndProvider($testModel, $resources);
                break;
            case 'geonames':
                $this->testGeonamesProvider($testModel, $resources);
                break;
            case 'metagrid':
                $this->testMetagridProvider($testModel, $resources);
                break;
            case 'idiotikon':
                $this->testIdiotikonProvider($testModel, $resources);
                break;
            case 'ortsnamen':
                $this->testOrtsnamenProvider($testModel, $resources);
                break;
            case 'georgfischer':
            case 'kba':
            case 'gosteli':
                $this->testAntonProviders($testModel, $resources, [$provider]);
                break;
            default:
                $this->error("Unknown provider: {$provider}");
                $this->info("Run 'php artisan resources-components:test-resources --list' to see all available providers");
                break;
        }
    }
    
    /**
     * Clean up created resources
     */
    protected function cleanup($testModel, Collection $resources)
    {
        $this->info("\nCleaning up created resources...");
        
        foreach ($resources as $resource) {
            $resource->delete();
        }
        
        $this->info("All test resources have been removed.");
    }
    
    /**
     * Create a test model for storing resources
     */
    protected function createTestModel()
    {
        $this->info('Creating test model...');
        
        // Check if the test_models table exists, create if not
        if (!Schema::hasTable('test_models')) {
            $this->info('Creating test_models table...');
            Schema::create('test_models', function ($table) {
                $table->id();
                $table->string('name');
            });
        }
        
        // Check if the resources table exists, create if not
        if (!Schema::hasTable('resources')) {
            $this->info('Creating resources table...');
            Schema::create('resources', function ($table) {
                $table->id();
                $table->string('provider');
                $table->string('provider_id');
                $table->string('url');
                $table->morphs('resourceable');
                $table->json('full_json')->nullable();
                $table->timestamps();
                
                $table->unique(['provider', 'provider_id', 'resourceable_type', 'resourceable_id']);
            });
        }
        
        // Create or retrieve a test model instance
        $testModel = \KraenzleRitter\ResourcesComponents\TestModel::firstOrCreate(
            ['name' => 'Test Model for Resources']
        );
        
        $this->info("Test model created with ID: {$testModel->id}");
        return $testModel;
    }
    
    /**
     * Test Wikipedia providers (de, en, fr, it)
     */
    protected function testWikipediaProviders($testModel, Collection $resources, array $providerKeys = null)
    {
        $this->info('Testing Wikipedia providers...');
        
        $wikipediaProviders = $providerKeys ?? ['wikipedia-de', 'wikipedia-en', 'wikipedia-fr', 'wikipedia-it'];
        $searchTerm = 'Albert Einstein';
        
        foreach ($wikipediaProviders as $providerKey) {
            $this->info("Testing {$providerKey}...");
            
            try {
                $client = new Wikipedia();
                $locale = substr($providerKey, strlen('wikipedia-'));
                
                $queryOptions = [
                    'providerKey' => $providerKey,
                    'locale' => $locale,
                    'limit' => 1
                ];
                
                $results = $client->search($searchTerm, $queryOptions);
                
                if (!empty($results)) {
                    $firstResult = $results[0];
                    $title = $firstResult->title ?? 'Unknown';
                    $pageid = $firstResult->pageid ?? '';
                    
                    // Build the URL using target_url from config
                    $targetUrlTemplate = config("resources-components.providers.{$providerKey}.target_url");
                    $underscoredName = str_replace(' ', '_', $title);
                    $url = str_replace(
                        ['{provider_id}', '{underscored_name}'], 
                        [$pageid, $underscoredName], 
                        $targetUrlTemplate
                    );
                    
                    $data = [
                        'provider' => strtolower($providerKey),
                        'provider_id' => $pageid,
                        'url' => $url
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added {$providerKey} resource: {$title} ({$url})");
                } else {
                    $this->error("No results found for {$providerKey}");
                }
            } catch (\Exception $e) {
                $this->error("Error with {$providerKey}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test Wikidata provider
     */
    protected function testWikidataProvider($testModel, Collection $resources)
    {
        $this->info('Testing Wikidata provider...');
        
        $searchTerm = 'Albert Einstein';
        
        try {
            $client = new Wikidata();
            $results = $client->search($searchTerm, ['limit' => 1]);
            
            if (!empty($results)) {
                $firstResult = $results[0] ?? null;
                if ($firstResult) {
                    $title = $firstResult->label ?? 'Unknown';
                    $id = $firstResult->id ?? '';
                    
                    // Build the URL using target_url from config
                    $targetUrlTemplate = config("resources-components.providers.wikidata.target_url");
                    $url = str_replace('{provider_id}', $id, $targetUrlTemplate);
                    
                    $data = [
                        'provider' => 'wikidata',
                        'provider_id' => $id,
                        'url' => $url
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added Wikidata resource: {$title} ({$url})");
                }
            } else {
                $this->error("No results found for Wikidata");
            }
        } catch (\Exception $e) {
            $this->error("Error with Wikidata: " . $e->getMessage());
        }
    }
    
    /**
     * Test GND provider
     */
    protected function testGndProvider($testModel, Collection $resources)
    {
        $this->info('Testing GND provider...');
        
        $searchTerm = 'Albert Einstein';
        
        try {
            $client = new Gnd();
            $results = $client->search($searchTerm, ['limit' => 1]);
            
            if (!empty($results->member)) {
                $firstResult = $results->member[0] ?? null;
                if ($firstResult) {
                    $title = $firstResult->preferredName ?? 'Unknown';
                    $id = substr($firstResult->id, strrpos($firstResult->id, '/') + 1);
                    
                    // Build the URL using target_url from config
                    $targetUrlTemplate = config("resources-components.providers.gnd.target_url");
                    $url = str_replace('{provider_id}', $id, $targetUrlTemplate);
                    
                    $data = [
                        'provider' => 'gnd',
                        'provider_id' => $id,
                        'url' => $url
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added GND resource: {$title} ({$url})");
                }
            } else {
                $this->error("No results found for GND");
            }
        } catch (\Exception $e) {
            $this->error("Error with GND: " . $e->getMessage());
        }
    }
    
    /**
     * Test Geonames provider
     */
    protected function testGeonamesProvider($testModel, Collection $resources)
    {
        $this->info('Testing Geonames provider...');
        
        $searchTerm = 'Zürich';
        
        // Check if Geonames username is set
        $userName = config("resources-components.providers.geonames.user_name");
        if (empty($userName) || $userName === 'demo') {
            $this->warn("Skipping Geonames - No valid username found. Set the GEONAMES_USERNAME environment variable.");
            return;
        }
        
        try {
            $client = new Geonames();
            $results = $client->search($searchTerm, ['limit' => 1]);
            
            if (!empty($results)) {
                $firstResult = $results[0] ?? null;
                if ($firstResult) {
                    $title = $firstResult->name ?? 'Unknown';
                    $id = $firstResult->geonameId ?? '';
                    
                    // Build the URL using target_url from config
                    $targetUrlTemplate = config("resources-components.providers.geonames.target_url");
                    $url = str_replace('{provider_id}', $id, $targetUrlTemplate);
                    
                    $data = [
                        'provider' => 'geonames',
                        'provider_id' => $id,
                        'url' => $url,
                        'full_json' => json_decode(json_encode($firstResult), true)
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added Geonames resource: {$title} ({$url})");
                }
            } else {
                $this->error("No results found for Geonames");
            }
        } catch (\Exception $e) {
            $this->error("Error with Geonames: " . $e->getMessage());
        }
    }
    
    /**
     * Test Metagrid provider
     */
    protected function testMetagridProvider($testModel, Collection $resources)
    {
        $this->info('Testing Metagrid provider...');
        
        $searchTerm = 'Albert Einstein';
        
        try {
            $client = new Metagrid();
            $results = $client->search($searchTerm, ['limit' => 1]);
            
            if (!empty($results)) {
                $firstResult = $results[0] ?? null;
                if ($firstResult) {
                    $title = $firstResult->name ?? 'Unknown';
                    $id = $firstResult->id ?? '';
                    
                    // Debug output to inspect the structure
                    $this->info("Metagrid firstResult structure: " . json_encode($firstResult));
                    
                    // Build the URL using target_url from config or direct URI from result
                    $targetUrlTemplate = config("resources-components.providers.metagrid.target_url");
                    $url = '';
                    
                    if ($targetUrlTemplate) {
                        $url = str_replace('{provider_id}', $id, $targetUrlTemplate);
                    } else {
                        // Try to get URL from resources property
                        if (isset($firstResult->resources) && is_array($firstResult->resources) && !empty($firstResult->resources)) {
                            foreach ($firstResult->resources as $resource) {
                                if (isset($resource->uri) && !empty($resource->uri)) {
                                    $url = $resource->uri;
                                    break;
                                }
                            }
                        }
                        
                        // If still no URL, try to get from provider_url if exists
                        if (empty($url) && isset($firstResult->provider_url)) {
                            $url = $firstResult->provider_url;
                        }
                        
                        // If still no URL, construct a default one
                        if (empty($url) && !empty($id)) {
                            $url = "https://metagrid.ch/widget/" . $id;
                        }
                    }
                    
                    $this->info("Metagrid URL constructed: " . $url);
                    
                    // Only proceed if we have both ID and URL
                    if (empty($id) || empty($url)) {
                        $this->error("Missing required data for Metagrid: ID = '{$id}', URL = '{$url}'");
                        return;
                    }
                    
                    $data = [
                        'provider' => 'metagrid',
                        'provider_id' => $id,
                        'url' => $url
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added Metagrid resource: {$title} ({$url})");
                }
            } else {
                $this->error("No results found for Metagrid");
            }
        } catch (\Exception $e) {
            $this->error("Error with Metagrid: " . $e->getMessage());
        }
    }
    
    /**
     * Test Idiotikon provider
     */
    protected function testIdiotikonProvider($testModel, Collection $resources)
    {
        $this->info('Testing Idiotikon provider...');
        
        $searchTerm = 'Allmend';
        
        try {
            $client = new Idiotikon();
            $results = $client->search($searchTerm, ['limit' => 1]);
            
            if (!empty($results)) {
                $firstResult = $results[0] ?? null;
                if ($firstResult) {
                    $title = $firstResult->lemma ?? 'Unknown';
                    
                    // Make sure we have a valid ID
                    // Debug the firstResult to see its structure
                    $this->info("Idiotikon result structure: " . json_encode($firstResult));
                    
                    // Try to extract ID from different possible properties
                    $id = '';
                    if (isset($firstResult->id)) {
                        $id = $firstResult->id;
                    } else if (isset($firstResult->lemmaId)) {
                        $id = $firstResult->lemmaId;
                    } else if (isset($firstResult->lemma_id)) {
                        $id = $firstResult->lemma_id;
                    }
                    
                    // If still no ID, try to extract from the URL if available
                    if (empty($id) && isset($firstResult->url)) {
                        $urlParts = explode('/', $firstResult->url);
                        $id = end($urlParts);
                    }
                    
                    $this->info("Idiotikon ID extracted: " . $id);
                    
                    // Build the URL using target_url from config
                    $targetUrlTemplate = config("resources-components.providers.idiotikon.target_url");
                    $url = $targetUrlTemplate ? 
                        str_replace('{provider_id}', $id, $targetUrlTemplate) :
                        "https://digital.idiotikon.ch/p/lem/{$id}";
                    
                    // Only proceed if we have both ID and URL
                    if (empty($id) || empty($url)) {
                        $this->error("Missing required data for Idiotikon: ID = '{$id}', URL = '{$url}'");
                        return;
                    }
                    
                    $data = [
                        'provider' => 'idiotikon',
                        'provider_id' => $id,
                        'url' => $url,
                        'full_json' => json_decode(json_encode($firstResult), true)
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added Idiotikon resource: {$title} ({$url})");
                }
            } else {
                $this->error("No results found for Idiotikon");
            }
        } catch (\Exception $e) {
            $this->error("Error with Idiotikon: " . $e->getMessage());
        }
    }
    
    /**
     * Test Ortsnamen provider
     */
    protected function testOrtsnamenProvider($testModel, Collection $resources)
    {
        $this->info('Testing Ortsnamen provider...');
        
        $searchTerm = 'Zürich';
        
        try {
            $client = new Ortsnamen();
            $results = $client->search($searchTerm, ['limit' => 1]);
            
            if (!empty($results)) {
                $firstResult = $results[0] ?? null;
                if ($firstResult) {
                    $title = $firstResult->name ?? 'Unknown';
                    $id = $firstResult->id ?? '';
                    
                    // Build the URL using target_url from config
                    $targetUrlTemplate = config("resources-components.providers.ortsnamen.target_url");
                    $url = str_replace('{provider_id}', $id, $targetUrlTemplate);
                    
                    $data = [
                        'provider' => 'ortsnamen',
                        'provider_id' => $id,
                        'url' => $url,
                        'full_json' => json_decode(json_encode($firstResult), true)
                    ];
                    
                    $resource = $testModel->updateOrCreateResource($data);
                    $resources->push($resource);
                    
                    $this->info("Added Ortsnamen resource: {$title} ({$url})");
                }
            } else {
                $this->error("No results found for Ortsnamen");
            }
        } catch (\Exception $e) {
            $this->error("Error with Ortsnamen: " . $e->getMessage());
        }
    }
    
    /**
     * Test Anton providers (georgfischer, kba, gosteli)
     */
    protected function testAntonProviders($testModel, Collection $resources, array $providerKeys = null)
    {
        $this->info('Testing Anton providers...');
        
        $allAntonProviders = [
            // Verwende einfachere Suchbegriffe und spezifischere Endpoints
            'georgfischer' => ['search' => 'ag', 'endpoint' => 'actors'],
            'kba' => ['search' => 'Karl', 'endpoint' => 'actors'],
            'gosteli' => ['search' => 'archiv', 'endpoint' => 'actors']
        ];
        
        // If specific providers are requested, filter the list
        if ($providerKeys) {
            $antonProviders = array_intersect_key($allAntonProviders, array_flip($providerKeys));
        } else {
            $antonProviders = $allAntonProviders;
        }
        
        foreach ($antonProviders as $providerKey => $data) {
            $this->info("Testing {$providerKey}...");
            
            // None of the Anton providers require an API token
            
            try {
                // Test direct access to API endpoint first
                $baseApiUrl = config("resources-components.providers.{$providerKey}.base_url");
                $testUrl = rtrim($baseApiUrl, '/') . '/' . $data['endpoint'] . '?search=' . urlencode($data['search']) . '&perPage=1';
                
                $this->info("Testing direct API access to: {$testUrl}");
                
                // Use Guzzle directly to test the API
                $httpClient = new \GuzzleHttp\Client();
                try {
                    $testResponse = $httpClient->get($testUrl);
                    $this->info("Direct API test status: " . $testResponse->getStatusCode());
                    $testContent = (string) $testResponse->getBody();
                    $this->info("Direct API response (truncated): " . substr($testContent, 0, 100) . "...");
                } catch (\Exception $directE) {
                    $this->error("Direct API test failed: " . $directE->getMessage());
                }
                
                // Now try with our Anton client
                $client = new Anton($providerKey);
                $this->info("Searching for '{$data['search']}' in endpoint '{$data['endpoint']}'...");
                $this->info("Provider URL: " . $baseApiUrl);
                
                $results = $client->search($data['search'], ['limit' => 1], $data['endpoint']);
                
                $this->info("Search completed. Result count: " . (is_array($results) ? count($results) : 'not an array'));
                $this->info("Results type: " . gettype($results));
                
                // Debug raw results (limiting to avoid huge output)
                $jsonResults = json_encode($results);
                $this->info("Raw results (truncated): " . substr($jsonResults, 0, 150) . (strlen($jsonResults) > 150 ? "..." : ""));
                
                if (!empty($results)) {
                    $firstResult = $results[0] ?? null;
                    if ($firstResult) {
                        $title = $firstResult->fullname ?? $firstResult->title ?? 'Unknown';
                        $id = $firstResult->id ?? '';
                        
                        // Get slug from config
                        $slug = config("resources-components.providers.{$providerKey}.slug", $providerKey);
                        
                        // Build the full provider ID
                        $fullProviderId = $slug . '-' . $data['endpoint'] . '-' . $id;
                        
                        // Build the URL using target_url from config
                        $targetUrlTemplate = config("resources-components.providers.{$providerKey}.target_url");
                        $url = str_replace(
                            ['{endpoint}', '{short_provider_id}'], 
                            [$data['endpoint'], $id], 
                            $targetUrlTemplate
                        );
                        
                        $dataToSave = [
                            'provider' => $providerKey,
                            'provider_id' => $fullProviderId,
                            'url' => $url,
                            'full_json' => json_decode(json_encode($firstResult), true)
                        ];
                        
                        $resource = $testModel->updateOrCreateResource($dataToSave);
                        $resources->push($resource);
                        
                        $this->info("Added {$providerKey} resource: {$title} ({$url})");
                    }
                } else {
                    $this->error("No results found for {$providerKey}");
                }
            } catch (\Exception $e) {
                $this->error("Error with {$providerKey}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Display all resources in a table
     */
    protected function displayResults(Collection $resources)
    {
        $this->info("\nResources created:");
        
        $headers = ['ID', 'Provider', 'Provider ID', 'URL'];
        $rows = [];
        
        foreach ($resources as $resource) {
            $rows[] = [
                $resource->id,
                $resource->provider,
                $resource->provider_id,
                $resource->url
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info("\nTotal resources created: " . count($resources));
    }
}
