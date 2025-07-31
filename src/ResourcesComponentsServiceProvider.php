<?php

namespace KraenzleRitter\ResourcesComponents;

use Illuminate\Support\ServiceProvider;

use KraenzleRitter\ResourcesComponents\GndLwComponent;
use KraenzleRitter\ResourcesComponents\MetagridLwComponent;
use KraenzleRitter\ResourcesComponents\WikidataLwComponent;
use KraenzleRitter\ResourcesComponents\WikipediaLwComponent;
use KraenzleRitter\ResourcesComponents\ProviderSelect;
use KraenzleRitter\ResourcesComponents\ResourcesList;
use KraenzleRitter\ResourcesComponents\Wikidata;
use KraenzleRitter\ResourcesComponents\Wikipedia;
use KraenzleRitter\ResourcesComponents\Gnd;
use KraenzleRitter\ResourcesComponents\Geonames;
use KraenzleRitter\ResourcesComponents\Metagrid;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Ortsnamen;
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'resources-components');

        // Load translation files
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'resources-components');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

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

        // Publishing assets is only necessary when using the CLI.
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

        // Register the service provided by the package.
        $this->app->singleton('resources-components', function ($app) {
            return new ResourcesComponents;
        });

        // Register Provider classes for dependency injection
        $this->app->bind(Wikidata::class);
        $this->app->bind(Wikipedia::class);
        $this->app->bind(Gnd::class);
        $this->app->bind(Geonames::class);
        $this->app->bind(Metagrid::class);
        $this->app->bind(Idiotikon::class);
        $this->app->bind(Ortsnamen::class);
        // Anton is handled separately as it requires parameters
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
        // Publishing the configuration file to the application.
        $this->publishes([
            __DIR__.'/../config/resources-components.php' => config_path('resources-components.php'),
        ], 'resources-components.config');

        // Publishing the views.
        // $this->publishes([
        //     __DIR__.'/../resources/views' => base_path('resources/views/vendor/kraenzle-ritter'),
        // ], 'resources-components.views');

        // Publishing the translation files to the application.
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/resources-components'),
        ], 'resources-components.lang');

        // Register commands
        $this->commands([
            \KraenzleRitter\ResourcesComponents\Commands\TestResourcesCommand::class
        ]);
    }
}
