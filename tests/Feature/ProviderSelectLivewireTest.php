<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use Livewire\Livewire;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use KraenzleRitter\ResourcesComponents\ProviderSelect;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class ProviderSelectLivewireTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Determine the actual path to the resources/views directory
        $packageRoot = dirname(__DIR__, 2); // Go up two levels from tests/Feature
        $viewsPath = $packageRoot . '/resources/views';
        
        // Original directory
        $app['view']->addNamespace(
            'resources-components',
            base_path('packages/kraenzle-ritter/resources-components/resources/views')
        );
        // Testbench shadow directory
        $app['view']->addNamespace(
            'resources-components',
            base_path('vendor/orchestra/testbench-core/laravel/packages/kraenzle-ritter/resources-components/resources/views')
        );
        // Dynamic path for GitHub Actions compatibility
        $app['view']->addNamespace(
            'resources-components',
            $viewsPath
        );
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }

    public function test_switches_provider_and_renders_component()
    {
        //dump(view()->getFinder()->getHints());
        //dump(glob(base_path('packages/kraenzle-ritter/resources-components/resources/views/livewire/*.blade.php')));
        $this->assertTrue(
            view()->exists('resources-components::livewire.provider-select'),
            'View resources-components::livewire.provider-select does not exist!'
        );
        $model = new DummyModel(['name' => 'Test']);
        $providers = ['georgfischer', 'kba'];

        Livewire::test(ProviderSelect::class, [
            'model' => $model,
            'providers' => $providers,
        ])
            ->call('setProvider', 'kba')
            ->assertSet('providerKey', 'kba');
    }
}
