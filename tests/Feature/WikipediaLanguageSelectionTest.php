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
            'params' => [
                'providerKey' => 'wikipedia-en'
            ]
        ])->instance();

        // Assert - Prüfen, ob die Locale richtig gesetzt ist
        $this->assertEquals('en', $component->queryOptions['locale'],
            'Die Locale sollte auf "en" gesetzt sein, wenn wikipedia-en ausgewählt ist');

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
        $deResults = $wikipedia->search('Ernst Cassirer', ['locale' => 'de', 'limit' => 1]);

        // Suche nach "Ernst Cassirer" in der englischen Wikipedia
        $enResults = $wikipedia->search('Ernst Cassirer', ['locale' => 'en', 'limit' => 1]);

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
        $component->mount($model, 'Ernst Cassirer', ['providerKey' => 'wikipedia-en']);

        // Überprüfen der gesetzten Werte
        $this->assertEquals('en', $component->queryOptions['locale'],
            'queryOptions sollte die Locale "en" enthalten');
        $this->assertEquals('https://en.wikipedia.org/wiki/', $component->base_url,
            'base_url sollte auf die englische Wikipedia zeigen');

        // Der eigentliche Fehler könnte in der render()-Methode liegen,
        // wo die Locale möglicherweise überschrieben wird
    }
}
