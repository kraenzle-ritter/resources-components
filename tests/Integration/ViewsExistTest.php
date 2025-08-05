<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Integration;

use Illuminate\Support\Facades\View;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;

class ViewsExistTest extends TestCase
{
    /**
     * Tests if all package views can be found.
     *
     * @return void
     */
    public function test_all_component_views_exist()
    {
        // List of views to check
        $views = [
            // Livewire-Komponenten-Views
            'resources-components::livewire.provider-select',
            'resources-components::livewire.resources-list',
            'resources-components::livewire.anton-lw-component',
            'resources-components::livewire.geonames-lw-component',
            'resources-components::livewire.gnd-lw-component',
            'resources-components::livewire.idiotikon-lw-component',
            'resources-components::livewire.metagrid-lw-component',
            'resources-components::livewire.ortsnamen-lw-component',
            'resources-components::livewire.wikidata-lw-component',
            'resources-components::livewire.wikipedia-lw-component',
            'resources-components::livewire.manual-input-lw-component',
        ];

        // Check each view
        foreach ($views as $view) {
            $this->assertTrue(
                View::exists($view),
                "The view '{$view}' could not be found"
            );
        }
    }

    /**
     * Tests if the ResourcesComponentsServiceProvider registers views correctly.
     *
     * @return void
     */
    public function test_provider_registers_views_correctly()
    {
        // Test if the provider correctly registers the views
        $this->assertTrue(
            View::exists('resources-components::livewire.provider-select'),
            "The ServiceProvider did not register views correctly"
        );

        // Since we only want to check if the views exist and are correctly registered,
        // we skip actually rendering them, as that would require data.
    }

    /**
     * Tests if both components can find their views.
     *
     * @return void
     */
    public function test_components_can_find_their_views()
    {
        // We only check if the views exist, without rendering them
        $this->assertTrue(
            View::exists('resources-components::livewire.resources-list'),
            "The view 'resources-list' could not be found"
        );

        $this->assertTrue(
            View::exists('resources-components::livewire.provider-select'),
            "The view 'provider-select' could not be found"
        );
    }

    /**
     * Tests if the components use the correct view paths.
     * This test checks the component code for the correct view path syntax.
     *
     * @return void
     */
    public function test_component_view_paths_are_correct()
    {
        $basePath = dirname(__DIR__, 2) . '/src/';
        $componentFiles = [
            $basePath . 'AntonLwComponent.php',
            $basePath . 'GeonamesLwComponent.php',
            $basePath . 'GndLwComponent.php',
            $basePath . 'IdiotikonLwComponent.php',
            $basePath . 'ManualInputLwComponent.php',
            $basePath . 'MetagridLwComponent.php',
            $basePath . 'OrtsnamenLwComponent.php',
            $basePath . 'ProviderSelect.php',
            $basePath . 'ResourcesList.php',
            $basePath . 'WikidataLwComponent.php',
            $basePath . 'WikipediaLwComponent.php',
        ];

        // Array of view paths and their corresponding components
        $viewMap = [
            'AntonLwComponent' => 'resources-components::livewire.anton-lw-component',
            'GeonamesLwComponent' => 'resources-components::livewire.geonames-lw-component',
            'GndLwComponent' => 'resources-components::livewire.gnd-lw-component',
            'IdiotikonLwComponent' => 'resources-components::livewire.idiotikon-lw-component',
            'ManualInputLwComponent' => 'resources-components::livewire.manual-input-lw-component',
            'MetagridLwComponent' => 'resources-components::livewire.metagrid-lw-component',
            'OrtsnamenLwComponent' => 'resources-components::livewire.ortsnamen-lw-component',
            'ProviderSelect' => 'resources-components::livewire.provider-select',
            'ResourcesList' => 'resources-components::livewire.resources-list',
            'WikidataLwComponent' => 'resources-components::livewire.wikidata-lw-component',
            'WikipediaLwComponent' => 'resources-components::livewire.wikipedia-lw-component',
        ];

        foreach ($componentFiles as $file) {
            // Ensure the file exists before trying to read it
            $this->assertFileExists($file, "Component file {$file} does not exist");
            
            $content = file_get_contents($file);
            $componentName = pathinfo($file, PATHINFO_FILENAME);

            // Get the expected view path from the map
            $expectedViewPath = $viewMap[$componentName];

            $this->assertStringContainsString(
                $expectedViewPath,
                $content,
                "The component {$componentName} does not use the correct view path. Expected: {$expectedViewPath}"
            );
        }
    }
}
