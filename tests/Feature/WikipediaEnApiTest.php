<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use Illuminate\Support\Facades\Http;

class WikipediaEnApiTest extends TestCase
{
    protected $skipIfNoInternet = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip test if internet connection is not available
        if ($this->skipIfNoInternet) {
            try {
                $connection = @fsockopen("www.wikipedia.org", 80);
                if (!$connection) {
                    $this->markTestSkipped('No internet connection available');
                }
                fclose($connection);
            } catch (\Exception $e) {
                $this->markTestSkipped('No internet connection available');
            }
        }
    }

    public function test_wikipedia_en_search_returns_expected_structure()
    {
        // Arrange
        $wikipedia = new Wikipedia();
        $searchTerm = 'Albert Einstein';

        // Die konfigurierte URL für Wikipedia-EN überprüfen
        $configuredUrl = config('resources-components.providers.wikipedia-en.base_url');
        $this->assertEquals('https://en.wikipedia.org/w/api.php', $configuredUrl,
            'Die konfigurierte URL für Wikipedia-EN ist nicht korrekt');

        // Act
        $results = $wikipedia->search($searchTerm, ['locale' => 'en', 'limit' => 3]);

        // Assert
        $this->assertNotNull($results, 'Wikipedia search should return results');
        $this->assertNotEmpty($results, 'Wikipedia search results should not be empty');
        $this->assertLessThanOrEqual(3, count($results), 'Wikipedia search should respect the limit parameter');

        // Check structure of the first result
        $firstResult = $results[0];
        $this->assertTrue(property_exists($firstResult, 'title'), 'Result should have a title');
        $this->assertTrue(property_exists($firstResult, 'snippet'), 'Result should have a snippet');
        $this->assertTrue(property_exists($firstResult, 'pageid'), 'Result should have a pageid');

        // Check if Albert Einstein is actually found
        $foundEinstein = false;
        foreach ($results as $result) {
            if (stripos($result->title, 'Einstein') !== false) {
                $foundEinstein = true;
                break;
            }
        }
        $this->assertTrue($foundEinstein, 'Wikipedia search should find Albert Einstein');
    }

    public function test_wikipedia_en_article_returns_expected_structure()
    {
        // Arrange
        $wikipedia = new Wikipedia();
        $title = 'Albert Einstein';

        // Die konfigurierte URL für Wikipedia-EN überprüfen
        $configuredUrl = config('resources-components.providers.wikipedia-en.base_url');
        $this->assertEquals('https://en.wikipedia.org/w/api.php', $configuredUrl,
            'Die konfigurierte URL für Wikipedia-EN ist nicht korrekt');

        // Act - Übergebe die Locale als Parameter
        $article = $wikipedia->getArticle($title, ['locale' => 'en']);        // Assert
        $this->assertNotNull($article, 'Wikipedia article should not be null');
        $this->assertTrue(property_exists($article, 'title'), 'Article should have a title');
        $this->assertTrue(property_exists($article, 'extract'), 'Article should have an extract');
        $this->assertTrue(property_exists($article, 'pageid'), 'Article should have a pageid');

        // Check article content
        $this->assertStringContainsString('Einstein', $article->title, 'Article title should contain Einstein');
        $this->assertNotEmpty($article->extract, 'Article extract should not be empty');
        // Der englische Artikel sollte Wörter enthalten, die nur in der englischen Version vorkommen
        $this->assertTrue(
            stripos($article->extract, 'physicist') !== false ||
            stripos($article->extract, 'physics') !== false ||
            stripos($article->extract, 'theory') !== false,
            'Einstein article should contain English terms'
        );
    }
}
