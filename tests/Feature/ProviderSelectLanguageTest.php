<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use Livewire\Livewire;
use KraenzleRitter\ResourcesComponents\ProviderSelect;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class ProviderSelectLanguageTest extends TestCase
{
    /**
     * @test
     */
    public function provider_select_correctly_passes_providerKey_to_wikipedia_component()
    {
        // Arrange
        $model = new DummyModel(['name' => 'Ernst Cassirer']);

        // Act - Simulieren der Benutzerauswahl von "Wikipedia (en)"
        $component = Livewire::test(ProviderSelect::class, [
            'model' => $model,
            'providers' => ['wikipedia-en', 'wikipedia-de'],
        ]);

        // Initial sollte der erste Provider (wikipedia-en) ausgewählt sein
        $component->assertSet('providerKey', 'wikipedia-en');

        // Prüfen, ob die Komponenten-Parameter korrekt sind
        $component->assertSet('componentParams.providerKey', 'wikipedia-en');

        // Jetzt explizit auf "wikipedia-en" umschalten
        $component->call('setProvider', 'wikipedia-en');

        // Prüfen, ob die richtigen Parameter an die Wikipedia-Komponente übergeben werden
        $component->assertSet('providerKey', 'wikipedia-en');
        $component->assertSet('componentParams.providerKey', 'wikipedia-en');

        // Die Komponente sollte die Livewire-Wikipedia-Komponente laden
        $component->assertSet('componentToRender', 'wikipedia-lw-component');
    }

    /**
     * @test
     */
    public function provider_parameters_are_correctly_passed_from_provider_select_to_component()
    {
        // Die Schlüsselstelle finden: Überprüfen der übergebenen Parameter

        // Arrange
        $model = new DummyModel(['name' => 'Test']);

        // Act - Test mit beiden Wikipedia-Varianten
        $enComponent = Livewire::test(ProviderSelect::class, [
            'model' => $model,
            'providers' => ['wikipedia-en'],
        ]);

        $deComponent = Livewire::test(ProviderSelect::class, [
            'model' => $model,
            'providers' => ['wikipedia-de'],
        ]);

        // Assert - English
        $enComponent->assertSet('providerKey', 'wikipedia-en');
        $enComponent->assertSet('componentParams', [
            'model' => $model,
            'search' => $model->name,
            'providerKey' => 'wikipedia-en',
        ]);

        // Assert - German
        $deComponent->assertSet('providerKey', 'wikipedia-de');
        $deComponent->assertSet('componentParams', [
            'model' => $model,
            'search' => $model->name,
            'providerKey' => 'wikipedia-de',
        ]);
    }
}
