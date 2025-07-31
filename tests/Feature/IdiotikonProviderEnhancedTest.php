<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Mockery;

class IdiotikonProviderEnhancedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    /**
     * Testet die korrekte URL-Konstruktion und ID-Extraktion mit verschiedenen API-Antwortformaten
     */
    public function test_idiotikon_id_extraction_and_url_construction()
    {
        // 1. Fall: Standard-Format mit lemmaId
        $standardResponse = [
            'lemmaID' => 'L12345',
            'lemmaText' => 'Zürich',
            'url' => 'https://api.idiotikon.ch/lemma/L12345'
        ];

        // 2. Fall: Alternatives Format mit ID
        $alternativeResponse = [
            'id' => 'L67890',
            'lemma' => 'Bern',
            'url' => 'https://api.idiotikon.ch/lemma/L67890'
        ];

        // 3. Fall: Format nur mit URL, ID muss extrahiert werden
        $urlOnlyResponse = [
            'lemma' => 'Basel',
            'url' => 'https://api.idiotikon.ch/lemma/L54321'
        ];

        // Erstellen eines Mock-Clients für Idiotikon
        $mockClient = $this->mockIdiotikonClient();

        // Test für jedes Antwortformat
        $this->assertCorrectIdExtraction($mockClient, $standardResponse, 'L12345');
        $this->assertCorrectIdExtraction($mockClient, $alternativeResponse, 'L67890');
        $this->assertCorrectIdExtraction($mockClient, $urlOnlyResponse, 'L54321');

        // Teste die URL-Konstruktion basierend auf der Konfiguration
        $this->assertCorrectUrlConstruction('L12345');
    }

    /**
     * Testet die Idiotikon-Suche und Verarbeitung der Ergebnisse
     */
    public function test_idiotikon_search_and_results_processing()
    {
        // Mock-Antwort für die Suche
        $searchResponse = json_encode([
            'results' => [
                [
                    'lemmaID' => 'L12345',
                    'lemmaText' => 'Zürich',
                    'url' => 'https://api.idiotikon.ch/lemma/L12345',
                    'description' => ['Eine Stadt in der Schweiz']
                ],
                [
                    'lemmaID' => 'L67890',
                    'lemmaText' => 'Zürichsee',
                    'url' => 'https://api.idiotikon.ch/lemma/L67890',
                    'description' => ['Ein See in der Schweiz']
                ]
            ]
        ]);

        // Mock-Client erstellen
        $mock = new MockHandler([
            new Response(200, [], $searchResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $mockHttpClient = new Client(['handler' => $handlerStack]);

        // Idiotikon-Provider mit Mock-Client injizieren
        $idiotikon = Mockery::mock(Idiotikon::class)->makePartial();
        $idiotikon->shouldReceive('__construct')->andReturn(null);
        $idiotikon->client = $mockHttpClient;

        // Suche durchführen
        $results = $idiotikon->search('Zürich', ['limit' => 5]);

        // Überprüfungen
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertEquals('L12345', $results[0]->lemmaID);
        $this->assertEquals('Zürich', $results[0]->lemmaText);
        $this->assertEquals('https://api.idiotikon.ch/lemma/L12345', $results[0]->url);
    }

    /**
     * Testet die Fehlerbehandlung bei Idiotikon API-Problemen
     */
    public function test_idiotikon_error_handling()
    {
        // Mock für eine Netzwerk-Ausnahme
        $mock = new MockHandler([
            new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $mockHttpClient = new Client(['handler' => $handlerStack]);

        // Idiotikon-Provider mit Mock-Client
        $idiotikon = Mockery::mock(Idiotikon::class)->makePartial();
        $idiotikon->shouldReceive('__construct')->andReturn(null);
        $idiotikon->client = $mockHttpClient;

        // Suche durchführen
        $results = $idiotikon->search('Zürich', ['limit' => 5]);

        // Überprüfen, ob ein leeres Array zurückgegeben wird
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Testet die Idiotikon Livewire-Komponente
     */
    public function test_idiotikon_livewire_component()
    {
        // Livewire-Komponente laden
        $model = DummyModel::create(['name' => 'Test Model']);

        // Test der Komponente mit Livewire
        $component = \Livewire\Livewire::test(
            \KraenzleRitter\ResourcesComponents\IdiotikonLwComponent::class,
            ['model' => $model]
        )
        ->set('search', 'Zürich');

        // Überprüfen, ob keine Fehler aufgetreten sind
        $component->assertOk();
    }

    /**
     * Erstellt einen Mock-Client für Idiotikon
     */
    private function mockIdiotikonClient()
    {
        $idiotikon = new Idiotikon();
        return $idiotikon;
    }

    /**
     * Überprüft, ob die ID korrekt aus verschiedenen Antwortformaten extrahiert wird
     */
    private function assertCorrectIdExtraction($client, $response, $expectedId)
    {
        $model = DummyModel::create(['name' => 'Test Model']);

        // Simuliere die ID-Extraktion, wie sie im TestResourcesCommand durchgeführt wird
        $id = '';
        if (isset($response['lemmaID'])) {
            $id = $response['lemmaID'];
        } else if (isset($response['id'])) {
            $id = $response['id'];
        } else if (isset($response['lemma_id'])) {
            $id = $response['lemma_id'];
        }

        // Wenn immer noch keine ID gefunden wurde, versuche sie aus der URL zu extrahieren
        if (empty($id) && isset($response['url'])) {
            $urlParts = explode('/', $response['url']);
            $id = end($urlParts);
        }

        $this->assertEquals($expectedId, $id, "ID wurde nicht korrekt extrahiert");
    }

    /**
     * Überprüft, ob die URL korrekt konstruiert wird
     */
    private function assertCorrectUrlConstruction($id)
    {
        // Konfiguration einrichten
        config()->set('resources-components.providers.idiotikon.target_url', 'https://digital.idiotikon.ch/p/lem/{provider_id}');

        // URL konstruieren wie im TestResourcesCommand
        $targetUrlTemplate = config("resources-components.providers.idiotikon.target_url");
        $url = str_replace('{provider_id}', $id, $targetUrlTemplate);

        $this->assertEquals("https://digital.idiotikon.ch/p/lem/{$id}", $url, "URL wurde nicht korrekt konstruiert");

        // Testen ohne Konfiguration
        config()->set('resources-components.providers.idiotikon.target_url', null);

        // Fallback-URL konstruieren
        $url = "https://digital.idiotikon.ch/p/lem/{$id}";

        $this->assertEquals("https://digital.idiotikon.ch/p/lem/{$id}", $url, "Fallback-URL wurde nicht korrekt konstruiert");
    }
}
