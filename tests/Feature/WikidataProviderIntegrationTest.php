<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use KraenzleRitter\ResourcesComponents\Wikidata;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class WikidataProviderIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    /**
     * Testet die Integration des Wikidata-Providers mit gemockten API-Antworten
     */
    public function test_wikidata_provider_integration()
    {
        // Mock für API-Antwort erstellen
        $mockResponse = json_encode([
            'searchinfo' => [
                'search' => 'Albert Einstein'
            ],
            'search' => [
                [
                    'id' => 'Q937',
                    'label' => 'Albert Einstein',
                    'description' => 'Physiker und Nobelpreisträger',
                    'match' => [
                        'type' => 'label',
                        'language' => 'de',
                        'text' => 'Albert Einstein'
                    ]
                ],
                [
                    'id' => 'Q17454793',
                    'label' => 'Albert Einstein Medal',
                    'description' => 'Auszeichnung der Albert-Einstein-Gesellschaft in Bern',
                    'match' => [
                        'type' => 'label',
                        'language' => 'de',
                        'text' => 'Albert Einstein Medal'
                    ]
                ]
            ]
        ]);

        // Mock-Handler einrichten
        $mock = new MockHandler([
            new Response(200, [], $mockResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Wikidata-Provider mit Mock-Client erstellen
        $wikidata = new Wikidata();
        $wikidata->client = $client;

        // Suche durchführen
        $results = $wikidata->search('Albert Einstein', ['locale' => 'de', 'limit' => 5]);

        // Überprüfen, ob Ergebnisse korrekt zurückgegeben werden
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertEquals('Q937', $results[0]->id);
        $this->assertEquals('Albert Einstein', $results[0]->title);
        $this->assertEquals('https://www.wikidata.org/wiki/Q937', $results[0]->url);

        // Fehlerbehandlung testen
        $errorMock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);
        $errorHandlerStack = HandlerStack::create($errorMock);
        $errorClient = new Client(['handler' => $errorHandlerStack]);
        $wikidata->client = $errorClient;

        // Fehlerhafte Suche durchführen
        $errorResults = $wikidata->search('Albert Einstein', ['locale' => 'de', 'limit' => 5]);

        // Prüfen, ob leeres Array zurückgegeben wird
        $this->assertIsArray($errorResults);
        $this->assertEmpty($errorResults);
    }

    /**
     * Testet die Namensumkehr-Funktion der Wikidata-Suche
     */
    public function test_wikidata_name_reversal()
    {
        // Mock für erste Anfrage (keine Ergebnisse)
        $emptyResponse = json_encode([
            'searchinfo' => ['search' => 'Einstein, Albert'],
            'search' => []
        ]);

        // Mock für zweite Anfrage (mit Ergebnissen)
        $successResponse = json_encode([
            'searchinfo' => ['search' => 'Albert Einstein'],
            'search' => [
                [
                    'id' => 'Q937',
                    'label' => 'Albert Einstein',
                    'description' => 'Physiker und Nobelpreisträger'
                ]
            ]
        ]);

        // Mock-Handler einrichten für beide Anfragen
        $mock = new MockHandler([
            new Response(200, [], $emptyResponse),
            new Response(200, [], $successResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Wikidata-Provider mit Mock-Client
        $wikidata = new Wikidata();
        $wikidata->client = $client;

        // Suche mit umgekehrtem Namen durchführen
        $results = $wikidata->search('Einstein, Albert', ['locale' => 'de', 'limit' => 5]);

        // Überprüfen, ob Ergebnisse nach der Namensumkehr zurückgegeben werden
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertEquals('Q937', $results[0]->id);
    }
}
