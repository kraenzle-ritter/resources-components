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
            'providerKey' => 'wikipedia-de' // Direkter Parameter statt innerhalb von params
        ])->instance();

        $this->assertEquals('wikipedia-de', $component->queryOptions['providerKey']);
        $this->assertEquals('https://de.wikipedia.org/wiki/', $component->base_url);

        // Testen der englischen Wikipedia
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin',
            'providerKey' => 'wikipedia-en' // Direkter Parameter statt innerhalb von params
        ])->instance();

        $this->assertEquals('wikipedia-en', $component->queryOptions['providerKey']);
        $this->assertEquals('https://en.wikipedia.org/wiki/', $component->base_url);
    }

    /** @test */
    public function it_uses_locale_parameter_as_fallback()
    {
        $model = new DummyModel();
        $model->name = 'Test';

        // Testen mit explizitem providerKey
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin',
            'providerKey' => 'wikipedia-fr' // Direkter Parameter statt innerhalb von params
        ])->instance();

        $this->assertEquals('wikipedia-fr', $component->queryOptions['providerKey']);
        $this->assertEquals('https://fr.wikipedia.org/wiki/', $component->base_url);
    }

    /** @test */
    public function it_defaults_to_german_when_no_locale_specified()
    {
        $model = new DummyModel();
        $model->name = 'Test';

        // Testen ohne providerKey-Parameter (Fallback auf Deutsch)
        $component = Livewire::test(WikipediaLwComponent::class, [
            'model' => $model,
            'search' => 'Berlin'
            // providerKey wird weggelassen, sollte auf 'wikipedia-de' zurückfallen
        ])->instance();

        $this->assertEquals('wikipedia-de', $component->queryOptions['providerKey']);
        $this->assertEquals('https://de.wikipedia.org/wiki/', $component->base_url);
    }
}
