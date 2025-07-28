<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class WikipediaTest extends TestCase
{
    protected Wikipedia $wikipedia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wikipedia = new Wikipedia();
    }

    /** @test */
    public function it_can_create_wikipedia_instance()
    {
        $this->assertInstanceOf(Wikipedia::class, $this->wikipedia);
        $this->assertInstanceOf(ProviderInterface::class, $this->wikipedia);
    }

    /** @test */
    public function it_returns_correct_provider_name()
    {
        $this->assertEquals('Wikipedia', $this->wikipedia->getProviderName());
    }

    /** @test */
    public function it_returns_correct_base_url()
    {
        $this->assertStringContainsString('wikipedia.org', $this->wikipedia->getBaseUrl());
    }

    /** @test */
    public function it_can_search_with_mocked_response()
    {
        $mockResponse = [
            'query' => [
                'searchinfo' => ['totalhits' => 1],
                'search' => [
                    [
                        'title' => 'Test Article',
                        'snippet' => 'Test snippet',
                        'pageid' => 12345
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

        $result = $this->wikipedia->search('Test');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test Article', $result[0]->title);
    }

    /** @test */
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

    /** @test */
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
