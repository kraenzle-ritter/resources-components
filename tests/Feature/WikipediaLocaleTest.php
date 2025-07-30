<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use Livewire\Livewire;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class WikipediaLocaleTest extends TestCase
{
    /** @test */
    public function it_uses_correct_locale_from_provider_key()
    {
        $model = new DummyModel();
        $model->name = 'Test';

        // Testen der deutschen Wikipedia (standard)
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin',
            'params' => [
                'providerKey' => 'wikipedia-de'
            ]
        ])->instance();

        $this->assertEquals('de', $component->queryOptions['locale']);
        $this->assertEquals('https://de.wikipedia.org/wiki/', $component->base_url);

        // Testen der englischen Wikipedia
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin',
            'params' => [
                'providerKey' => 'wikipedia-en'
            ]
        ])->instance();

        $this->assertEquals('en', $component->queryOptions['locale']);
        $this->assertEquals('https://en.wikipedia.org/wiki/', $component->base_url);
    }

    /** @test */
    public function it_uses_locale_parameter_as_fallback()
    {
        $model = new DummyModel();
        $model->name = 'Test';

        // Testen mit explizitem locale-Parameter und providerKey
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin',
            'params' => [
                'locale' => 'fr',
                'providerKey' => 'wikipedia-fr' // Dies ist wichtig für die neue Implementation
            ]
        ])->instance();

        $this->assertEquals('fr', $component->queryOptions['locale']);
        $this->assertEquals('https://fr.wikipedia.org/wiki/', $component->base_url);
    }

    /** @test */
    public function it_defaults_to_german_when_no_locale_specified()
    {
        $model = new DummyModel();
        $model->name = 'Test';

        // Testen ohne locale-Parameter
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin',
            'params' => []
        ])->instance();

        $this->assertEquals('de', $component->queryOptions['locale']);
        $this->assertEquals('https://de.wikipedia.org/wiki/', $component->base_url);
    }
}
