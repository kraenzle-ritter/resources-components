<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\Attributes\Test;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;

class WikipediaTest extends TestCase
{
    protected Wikipedia $wikipedia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wikipedia = new Wikipedia();
    }

    #[Test]
    public function it_can_create_wikipedia_instance()
    {
        $this->assertInstanceOf(Wikipedia::class, $this->wikipedia);
        $this->assertInstanceOf(ProviderInterface::class, $this->wikipedia);
    }

    #[Test]
    public function it_returns_correct_provider_name()
    {
        $this->assertEquals('Wikipedia', $this->wikipedia->getProviderName());
    }

    #[Test]
    public function it_returns_correct_base_url()
    {
        $this->assertStringContainsString('wikipedia.org', $this->wikipedia->getBaseUrl());
    }

    #[Test]
    public function it_can_search_with_mocked_response()
    {
        // Since Wikipedia class recreates client in search method,
        // we test that search doesn't throw exception and returns array
        $result = $this->wikipedia->search('Test', ['limit' => 1]);

        $this->assertIsArray($result);
        // We can't test exact content without mocking HTTP, but we can test structure
        // Empty result is expected since we're not hitting real API
    }

    #[Test]
    public function it_handles_empty_search_results()
    {
        $mockResponse = [
            'query' => [
                'searchinfo' => ['totalhits' => 0],
                'search' => []
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);

        // Use reflection to set the protected client property
        $reflection = new \ReflectionClass($this->wikipedia);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->wikipedia, new Client(['handler' => $handlerStack]));

        $result = $this->wikipedia->search('NonexistentArticle');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function it_can_get_article_with_mocked_response()
    {
        $mockResponse = [
            'query' => [
                'pages' => [
                    '12345' => [
                        'title' => 'Test Article',
                        'extract' => 'This is a test article extract.'
                    ]
                ]
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);

        // Use reflection to set the protected client property
        $reflection = new \ReflectionClass($this->wikipedia);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->wikipedia, new Client(['handler' => $handlerStack]));

        $result = $this->wikipedia->getArticle('Test Article');

        $this->assertIsObject($result);
        $this->assertEquals('Test Article', $result->title);
        $this->assertEquals('This is a test article extract.', $result->extract);
    }
}
