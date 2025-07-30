<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use Livewire\Livewire;
use Illuminate\Support\Facades\Config;

class WikipediaUrlLanguageTest extends TestCase
{
    /**
     * @test
     */
    public function it_correctly_sets_and_passes_base_url_to_view()
    {
        // Konfiguration für verschiedene Wikipedia-Provider definieren
        Config::set('resources-components.providers.wikipedia-de.base_url', 'https://de.wikipedia.org/w/api.php');
        Config::set('resources-components.providers.wikipedia-en.base_url', 'https://en.wikipedia.org/w/api.php');
        Config::set('resources-components.providers.wikipedia-fr.base_url', 'https://fr.wikipedia.org/w/api.php');

        // Verwende das DummyModel anstelle eines mocks
        $model = new DummyModel(['name' => 'Test']);
        $model->resources = collect([]);

        // Teste deutsche Wikipedia
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Test',
            'providerKey' => 'wikipedia-de' // Aktualisiert gemäß der neuen mount-Methode
        ]);

        $this->assertEquals('https://de.wikipedia.org/wiki/', $component->get('base_url'));

        // Teste englische Wikipedia
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Test',
            'providerKey' => 'wikipedia-en' // Aktualisiert gemäß der neuen mount-Methode
        ]);

        $this->assertEquals('https://en.wikipedia.org/wiki/', $component->get('base_url'));

        // Teste französische Wikipedia
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Test',
            'providerKey' => 'wikipedia-fr' // Aktualisiert gemäß der neuen mount-Methode
        ]);

        $this->assertEquals('https://fr.wikipedia.org/wiki/', $component->get('base_url'));
    }
}
