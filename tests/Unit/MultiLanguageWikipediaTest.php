<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Providers\MultiLanguageWikipediaProvider;
use KraenzleRitter\ResourcesComponents\Contracts\ProviderInterface;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class MultiLanguageWikipediaTest extends TestCase
{
    protected MultiLanguageWikipediaProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure minimal languages for testing
        $this->app['config']->set('resources-components.multilanguage-wikipedia.default_language', 'de');
        
        $this->provider = new MultiLanguageWikipediaProvider();
    }

    #[Test]
    public function it_can_create_multilanguage_wikipedia_instance()
    {
        $this->assertInstanceOf(MultiLanguageWikipediaProvider::class, $this->provider);
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    #[Test]
    public function it_returns_correct_provider_name()
    {
        $this->assertEquals('MultiLanguageWikipedia', $this->provider->getProviderName());
    }

    #[Test]
    public function it_has_correct_base_url_format()
    {
        $this->assertStringContainsString('wikipedia.org', $this->provider->getBaseUrl());
    }

    #[Test]
    public function it_returns_supported_languages()
    {
        $languages = $this->provider->getSupportedLanguages();
        
        $this->assertIsArray($languages);
        $this->assertArrayHasKey('de', $languages);
        $this->assertArrayHasKey('en', $languages);
        $this->assertArrayHasKey('fr', $languages);
        $this->assertEquals('Deutsch', $languages['de']);
        $this->assertEquals('English', $languages['en']);
    }

    #[Test]
    public function it_checks_language_support_correctly()
    {
        $this->assertTrue($this->provider->isLanguageSupported('de'));
        $this->assertTrue($this->provider->isLanguageSupported('en'));
        $this->assertFalse($this->provider->isLanguageSupported('xyz'));
    }

    #[Test]
    public function it_can_search_in_single_language()
    {
        // Mock successful search response
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'query' => [
                    'searchinfo' => ['totalhits' => 1],
                    'search' => [
                        (object)[
                            'title' => 'Test Article',
                            'snippet' => 'This is a test article'
                        ]
                    ]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $reflection = new \ReflectionClass($this->provider);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->provider, new Client(['handler' => $handlerStack]));

        $results = $this->provider->search('Test', ['languages' => ['de']]);

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals('Test Article', $results[0]->title);
        $this->assertEquals('de', $results[0]->language);
    }

    #[Test]
    public function it_can_search_in_multiple_languages()
    {
        // Mock multiple successful responses for different languages
        $mockHandler = new MockHandler([
            // First language (de)
            new Response(200, [], json_encode([
                'query' => [
                    'searchinfo' => ['totalhits' => 1],
                    'search' => [
                        (object)[
                            'title' => 'German Article',
                            'snippet' => 'German content'
                        ]
                    ]
                ]
            ])),
            // Second language (en)
            new Response(200, [], json_encode([
                'query' => [
                    'searchinfo' => ['totalhits' => 1],
                    'search' => [
                        (object)[
                            'title' => 'English Article',
                            'snippet' => 'English content'
                        ]
                    ]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $reflection = new \ReflectionClass($this->provider);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->provider, new Client(['handler' => $handlerStack]));

        $results = $this->provider->search('Test', ['languages' => ['de', 'en']]);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        
        // Check that results have language information
        $this->assertNotNull($results[0]->language);
        $this->assertNotNull($results[1]->language);
    }

    #[Test]
    public function it_returns_empty_array_for_no_results()
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'query' => [
                    'searchinfo' => ['totalhits' => 0],
                    'search' => []
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $reflection = new \ReflectionClass($this->provider);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->provider, new Client(['handler' => $handlerStack]));

        $results = $this->provider->search('NonexistentTerm');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    #[Test]
    public function it_can_get_article_in_specific_language()
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'query' => [
                    'pages' => [
                        '12345' => (object)[
                            'title' => 'Test Article',
                            'extract' => 'This is the article content'
                        ]
                    ]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $reflection = new \ReflectionClass($this->provider);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->provider, new Client(['handler' => $handlerStack]));

        $article = $this->provider->getArticle('Test Article', 'en');

        $this->assertNotNull($article);
        $this->assertEquals('Test Article', $article->title);
        $this->assertEquals('en', $article->language);
        $this->assertEquals('English', $article->language_name);
    }

    #[Test]
    public function it_skips_unsupported_languages()
    {
        $results = $this->provider->search('Test', ['languages' => ['xyz', 'invalid']]);
        
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
