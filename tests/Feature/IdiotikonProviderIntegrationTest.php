<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class IdiotikonProviderIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    /**
     * Testet die Integration des Idiotikon-Providers mit gemockten API-Antworten
     */
    public function test_idiotikon_provider_integration()
    {
        // Mock für API-Antwort erstellen
        $mockResponse = json_encode([
            'results' => [
                [
                    'lemmaID' => 'L12345',
                    'lemmaText' => 'Zürich',
                    'url' => 'https://api.idiotikon.ch/lemma/L12345',
                    'description' => ['Eine Stadt in der Schweiz']
                ]
            ]
        ]);

        // Mock-Handler einrichten
        $mock = new MockHandler([
            new Response(200, [], $mockResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Idiotikon-Provider mit Mock-Client erstellen
        $idiotikon = new Idiotikon();
        $idiotikon->client = $client;

        // Suche durchführen
        $results = $idiotikon->search('Zürich', ['limit' => 5]);

        // Überprüfen, ob Ergebnisse korrekt zurückgegeben werden
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertEquals('L12345', $results[0]->lemmaID);
        $this->assertEquals('Zürich', $results[0]->lemmaText);
        $this->assertEquals('https://api.idiotikon.ch/lemma/L12345', $results[0]->url);

        // ID-Extraktion testen - wie im Command implementiert
        $firstResult = $results[0];
        $id = '';

        // Extrahiere ID aus verschiedenen möglichen Eigenschaften
        if (isset($firstResult->lemmaID)) {
            $id = $firstResult->lemmaID;
        } else if (isset($firstResult->id)) {
            $id = $firstResult->id;
        } else if (isset($firstResult->lemma_id)) {
            $id = $firstResult->lemma_id;
        }

        // Prüfen, ob ID korrekt extrahiert wurde
        $this->assertEquals('L12345', $id);

        // URL-Konstruktion mit Konfiguration testen
        config()->set('resources-components.providers.idiotikon.target_url', 'https://digital.idiotikon.ch/p/lem/{provider_id}');
        $targetUrlTemplate = config('resources-components.providers.idiotikon.target_url');
        $url = str_replace('{provider_id}', $id, $targetUrlTemplate);

        // Prüfen, ob URL korrekt konstruiert wurde
        $this->assertEquals('https://digital.idiotikon.ch/p/lem/L12345', $url);

        // Fehlerbehandlung testen
        $errorMock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);
        $errorHandlerStack = HandlerStack::create($errorMock);
        $errorClient = new Client(['handler' => $errorHandlerStack]);
        $idiotikon->client = $errorClient;

        // Fehlerhafte Suche durchführen
        $errorResults = $idiotikon->search('Zürich', ['limit' => 5]);

        // Prüfen, ob leeres Array zurückgegeben wird
        $this->assertIsArray($errorResults);
        $this->assertEmpty($errorResults);
    }

    /**
     * Testet den Fall, wenn keine URL vorhanden ist und extrahiert werden muss
     */
    public function test_idiotikon_url_extraction_fallback()
    {
        // Mock-Antwort ohne explizite URL
        $mockResponse = json_encode([
            'results' => [
                [
                    'lemmaID' => 'L12345',
                    'lemmaText' => 'Zürich',
                    // Keine URL angegeben
                ]
            ]
        ]);

        // Mock-Handler einrichten
        $mock = new MockHandler([
            new Response(200, [], $mockResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Idiotikon-Provider mit Mock-Client
        $idiotikon = new Idiotikon();
        $idiotikon->client = $client;

        // Suche durchführen
        $results = $idiotikon->search('Zürich', ['limit' => 5]);
        $firstResult = $results[0];

        // ID extrahieren
        $id = '';
        if (isset($firstResult->lemmaID)) {
            $id = $firstResult->lemmaID;
        }

        // URL konstruieren wie im Command
        config()->set('resources-components.providers.idiotikon.target_url', 'https://digital.idiotikon.ch/p/lem/{provider_id}');
        $targetUrlTemplate = config('resources-components.providers.idiotikon.target_url');
        $url = str_replace('{provider_id}', $id, $targetUrlTemplate);

        // Prüfen, ob URL korrekt konstruiert wurde
        $this->assertEquals('https://digital.idiotikon.ch/p/lem/L12345', $url);

        // Ohne konfigurierte target_url testen
        config()->set('resources-components.providers.idiotikon.target_url', null);
        $targetUrlTemplate = config('resources-components.providers.idiotikon.target_url');
        $url = empty($targetUrlTemplate)
            ? "https://digital.idiotikon.ch/p/lem/{$id}"
            : str_replace('{provider_id}', $id, $targetUrlTemplate);

        // Prüfen, ob Standard-URL verwendet wird
        $this->assertEquals('https://digital.idiotikon.ch/p/lem/L12345', $url);
    }
}
