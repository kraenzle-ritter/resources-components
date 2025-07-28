<?php

namespace KraenzleRitter\ResourcesComponents;

use Illuminate\Support\ServiceProvider;

use KraenzleRitter\ResourcesComponents\GndLwComponent;
use KraenzleRitter\ResourcesComponents\MetagridLwComponent;
use KraenzleRitter\ResourcesComponents\WikidataLwComponent;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use KraenzleRitter\ResourcesComponents\ProviderSelect;
use KraenzleRitter\ResourcesComponents\ResourcesList;
use Livewire\Livewire;

class ResourcesComponentsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/livewire', 'resources-components');

        Livewire::component('provider-select', ProviderSelect::class);
        Livewire::component('resources-list', ResourcesList::class);

        Livewire::component('anton-lw-component', AntonLwComponent::class);
        Livewire::component('geonames-lw-component', GeonamesLwComponent::class);
        Livewire::component('gnd-lw-component', GndLwComponent::class);
        Livewire::component('idiotikon-lw-component', IdiotikonLwComponent::class);
        Livewire::component('metagrid-lw-component', MetagridLwComponent::class);
        Livewire::component('ortsnamen-lw-component', OrtsnamenLwComponent::class);
        Livewire::component('wikidata-lw-component', WikidataLwComponent::class);
        Livewire::component('wikipedia-lw-component', WikipediaLwComponent::class);
        Livewire::component('manual-input-lw-component', ManualInputLwComponent::class);

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/resources-components.php', 'resources-components');

        // Register the service the package provides.
        $this->app->singleton('resources-components', function ($app) {
            return new ResourcesComponents;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['provider-select', 'resources-list', 'anton-lw-component', 'geonames-lw-component', 'gnd-lw-component', 'metagrid-lw-component', 'ortsnamen-lw-component', 'wikidata-lw-component', 'wikipedia-lw-component', 'manual-input-lw-component'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Register console commands
        $this->commands([
            \KraenzleRitter\ResourcesComponents\Console\MakeProviderCommand::class,
        ]);

        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/resources-components.php' => config_path('resources-components.php'),
        ], 'resources-components.config');

        // Publishing the views.
        // $this->publishes([
        //     __DIR__.'/../resources/views' => base_path('resources/views/vendor/kraenzle-ritter'),
        // ], 'resources-components.views');
    }
}
