<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use Livewire\Livewire;
use KraenzleRitter\ResourcesComponents\ProviderSelect;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;

class ProviderSelectTest extends TestCase
{
    public function test_component_sets_and_renders_provider()
    {
        $model = new DummyModel(['name' => 'Test']);
        $providers = ['provider1', 'provider2'];

        Livewire::test(ProviderSelect::class, [
            'model' => $model,
            'providers' => $providers,
        ])
            ->call('setProvider', 'provider1')
            ->assertSet('providerKey', 'provider1');
    }
}
