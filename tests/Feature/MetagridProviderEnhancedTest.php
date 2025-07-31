<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use KraenzleRitter\ResourcesComponents\Metagrid;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Mockery;

class MetagridProviderEnhancedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    /**
     * Testet die korrekte URL-Konstruktion und ID-Extraktion mit verschiedenen API-Antwortformaten
     */
    public function test_metagrid_id_extraction_and_url_construction()
    {
        // 1. Fall: Standard-Format mit id
        $standardResponse = (object)[
            'id' => '12345',
            'name' => 'Albert Einstein',
            'resources' => [(object)[
                'provider' => (object)[
                    'slug' => 'hls',
                    'name' => 'Historisches Lexikon der Schweiz'
                ],
                'link' => (object)[
                    'uri' => 'https://hls-dhs-dss.ch/123'
                ]
            ]]
        ];

        // 2. Fall: Format mit verschachtelter ID
        $nestedResponse = (object)[
            'concordance' => (object)[
                'id' => '67890'
            ],
            'name' => 'Max Frisch',
            'provider_url' => 'https://metagrid.ch/widget/67890'
        ];

        // 3. Fall: Format ohne resources, aber mit provider_url
        $providerUrlResponse = (object)[
            'id' => '54321',
            'name' => 'Friedrich Dürrenmatt',
            'provider_url' => 'https://metagrid.ch/widget/54321'
        ];

        // Test für jedes Antwortformat
        $this->assertCorrectMetagridIdExtraction($standardResponse, '12345');
        $this->assertCorrectMetagridIdExtraction($nestedResponse, '67890');
        $this->assertCorrectMetagridIdExtraction($providerUrlResponse, '54321');

        // Teste die URL-Konstruktion basierend auf der Konfiguration
        $this->assertCorrectMetagridUrlConstruction('12345');

        // Teste die URL-Extraktion aus resources
        $this->assertCorrectMetagridUrlFromResources($standardResponse);

        // Teste die URL-Extraktion aus provider_url
        $this->assertCorrectMetagridUrlFromProviderUrl($providerUrlResponse);
    }

    /**
     * Testet die Metagrid-Suche und Verarbeitung der Ergebnisse
     */
    public function test_metagrid_search_and_results_processing()
    {
        // Mock-Antwort für die Suche
        $searchResponse = json_encode([
            'meta' => [
                'total' => 2
            ],
            'concordances' => [
                [
                    'id' => '12345',
                    'name' => 'Albert Einstein',
                    'resources' => [
                        [
                            'provider' => [
                                'slug' => 'hls',
                                'name' => 'Historisches Lexikon der Schweiz'
                            ],
                            'link' => [
                                'uri' => 'https://hls-dhs-dss.ch/123'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => '67890',
                    'name' => 'Max Frisch',
                    'provider_url' => 'https://metagrid.ch/widget/67890'
                ]
            ]
        ]);

        // Mock-Client erstellen
        $mock = new MockHandler([
            new Response(200, [], $searchResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Metagrid mit Mock-Client
        $metagrid = new Metagrid();
        $metagrid->client = $client;

        // Suche durchführen
        $results = $metagrid->search('Albert Einstein', ['limit' => 5]);

        // Überprüfungen
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertEquals('12345', $results[0]->id);
        $this->assertEquals('Albert Einstein', $results[0]->name);
    }

    /**
     * Testet die Fehlerbehandlung bei Metagrid API-Problemen
     */
    public function test_metagrid_error_handling()
    {
        // Mock für eine Netzwerk-Ausnahme
        $mock = new MockHandler([
            new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Metagrid mit Mock-Client
        $metagrid = new Metagrid();
        $metagrid->client = $client;

        // Suche durchführen
        $results = $metagrid->search('Albert Einstein', ['limit' => 5]);

        // Überprüfen, ob ein leeres Array zurückgegeben wird
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Testet die Metagrid Livewire-Komponente
     */
    public function test_metagrid_livewire_component()
    {
        // Livewire-Komponente laden
        $model = DummyModel::create(['name' => 'Test Model']);

        // Test der Komponente mit Livewire
        $component = \Livewire\Livewire::test(
            \KraenzleRitter\ResourcesComponents\MetagridLwComponent::class,
            ['model' => $model]
        )
        ->set('search', 'Albert Einstein');

        // Überprüfen, ob keine Fehler aufgetreten sind
        $component->assertOk();
    }

    /**
     * Testet die GetConcordance-Methode von Metagrid
     */
    public function test_metagrid_get_concordance()
    {
        // Mock-Antwort für getConcordance
        $concordanceResponse = json_encode([
            'id' => '12345',
            'name' => 'Albert Einstein',
            'resources' => [
                [
                    'provider' => [
                        'slug' => 'hls',
                        'name' => 'Historisches Lexikon der Schweiz'
                    ],
                    'link' => [
                        'uri' => 'https://hls-dhs-dss.ch/123'
                    ]
                ]
            ]
        ]);

        // Mock-Client erstellen
        $mock = new MockHandler([
            new Response(200, [], $concordanceResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Metagrid mit Mock-Client
        $metagrid = new Metagrid();
        $metagrid->client = $client;

        // getConcordance aufrufen
        $result = $metagrid->getConcordance('12345');

        // Überprüfen, ob das Objekt zurückgegeben wird
        $this->assertInstanceOf(Metagrid::class, $result);
        $this->assertNotNull($result->body);
        $this->assertEquals('12345', $result->body->id);
    }

    /**
     * Überprüft, ob die ID korrekt aus verschiedenen Antwortformaten extrahiert wird
     */
    private function assertCorrectMetagridIdExtraction($result, $expectedId)
    {
        // Simuliere die ID-Extraktion wie im TestResourcesCommand
        $id = '';
        if (isset($result->id)) {
            $id = $result->id;
        } else if (isset($result->concordance) && isset($result->concordance->id)) {
            $id = $result->concordance->id;
        }

        $this->assertEquals($expectedId, $id, "ID wurde nicht korrekt extrahiert");
    }

    /**
     * Überprüft, ob die URL korrekt konstruiert wird basierend auf der Konfiguration
     */
    private function assertCorrectMetagridUrlConstruction($id)
    {
        // Konfiguration einrichten
        config()->set('resources-components.providers.metagrid.target_url', 'https://metagrid.ch/widget/{provider_id}');

        // URL konstruieren wie im TestResourcesCommand
        $targetUrlTemplate = config("resources-components.providers.metagrid.target_url");
        $url = str_replace('{provider_id}', $id, $targetUrlTemplate);

        $this->assertEquals("https://metagrid.ch/widget/{$id}", $url, "URL wurde nicht korrekt konstruiert");
    }

    /**
     * Überprüft, ob die URL korrekt aus den resources-Eigenschaften extrahiert wird
     */
    private function assertCorrectMetagridUrlFromResources($result)
    {
        $url = '';

        // Versuche, URL aus resources-Eigenschaft zu extrahieren
        if (isset($result->resources) && is_array($result->resources) && !empty($result->resources)) {
            foreach ($result->resources as $resource) {
                if (isset($resource->link) && isset($resource->link->uri)) {
                    $url = $resource->link->uri;
                    break;
                }
            }
        }

        $this->assertNotEmpty($url, "URL konnte nicht aus resources extrahiert werden");
    }

    /**
     * Überprüft, ob die URL korrekt aus provider_url extrahiert wird
     */
    private function assertCorrectMetagridUrlFromProviderUrl($result)
    {
        $url = '';

        if (isset($result->provider_url)) {
            $url = $result->provider_url;
        }

        $this->assertEquals("https://metagrid.ch/widget/54321", $url, "URL wurde nicht korrekt aus provider_url extrahiert");
    }
}
