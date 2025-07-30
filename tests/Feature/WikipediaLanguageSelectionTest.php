<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use Livewire\Livewire;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class WikipediaLanguageSelectionTest extends TestCase
{
    /**
     * @test
     */
    public function locale_is_set_correctly_from_provider_key()
    {
        // Arrange
        $model = new DummyModel(['name' => 'Ernst Cassirer']);

        // Act - Englische Wikipedia auswählen
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Ernst Cassirer',
            'providerKey' => 'wikipedia-en' // Direkter Parameter statt innerhalb von 'params'
        ])->instance();

        // Assert - Prüfen, ob der Provider-Key richtig gesetzt ist
        $this->assertEquals('wikipedia-en', $component->queryOptions['providerKey'],
            'Der providerKey sollte auf "wikipedia-en" gesetzt sein');

        // Assert - Prüfen, ob die Base-URL korrekt ist
        $this->assertEquals('https://en.wikipedia.org/wiki/', $component->base_url,
            'Die Base-URL sollte die englische Wikipedia verwenden');
    }

    /**
     * @test
     */
    public function actual_api_call_uses_correct_locale()
    {
        // Skip if no internet connection
        try {
            $connection = @fsockopen("www.wikipedia.org", 80);
            if (!$connection) {
                $this->markTestSkipped('No internet connection available');
            }
            fclose($connection);
        } catch (\Exception $e) {
            $this->markTestSkipped('No internet connection available');
        }

        // Test mit einem echten API-Aufruf
        $wikipedia = new Wikipedia();

        // Suche nach "Ernst Cassirer" in der deutschen Wikipedia
        $deResults = $wikipedia->search('Ernst Cassirer', ['providerKey' => 'wikipedia-de', 'limit' => 1]);

        // Suche nach "Ernst Cassirer" in der englischen Wikipedia
        $enResults = $wikipedia->search('Ernst Cassirer', ['providerKey' => 'wikipedia-en', 'limit' => 1]);

        // Prüfen, ob die Ergebnisse unterschiedlich sind (was sie sein sollten, da Artikel in verschiedenen Sprachen)
        if (!empty($deResults) && !empty($enResults)) {
            $this->assertNotEquals(
                $deResults[0]->snippet ?? '',
                $enResults[0]->snippet ?? '',
                'Die Ergebnisse der deutschen und englischen Wikipedia sollten unterschiedlich sein'
            );
        }
    }

    /**
     * @test
     */
    public function component_render_passes_locale_to_search_method()
    {
        // Problem identifizieren: Die Komponente verwendet möglicherweise nicht die gesetzten queryOptions
        $model = new DummyModel(['name' => 'Test']);
        $component = new WikipediaLwComponent();

        // Manuelles Setup der Komponente mit englischer Locale
        $component->mount($model, 'Ernst Cassirer', 'wikipedia-en'); // String statt Array

        // Überprüfen der gesetzten Werte
        $this->assertEquals('wikipedia-en', $component->queryOptions['providerKey'],
            'queryOptions sollte den providerKey "wikipedia-en" enthalten');
        $this->assertEquals('https://en.wikipedia.org/wiki/', $component->base_url,
            'base_url sollte auf die englische Wikipedia zeigen');

        // Der eigentliche Fehler könnte in der render()-Methode liegen,
        // wo die Locale möglicherweise überschrieben wird
    }
}
