<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use KraenzleRitter\ResourcesComponents\Idiotikon;
use KraenzleRitter\ResourcesComponents\Metagrid;

class ResourcesProvidersCombinedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();

        // Stellen sicher, dass die resources-Tabelle existiert
        if (!Schema::hasTable('resources')) {
            Schema::create('resources', function ($table) {
                $table->increments('id');
                $table->string('provider');
                $table->string('provider_id');
                $table->string('url');
                $table->json('full_json')->nullable();
                $table->morphs('resourceable');
                $table->timestamps();
                $table->unique(['provider', 'provider_id', 'resourceable_id', 'resourceable_type']);
            });
        }
    }

    /**
     * Testet, ob die Konfiguration richtig geladen wird
     */
    public function test_config_loading()
    {
        $providers = Config::get('resources-components.providers');

        $this->assertNotEmpty($providers);
        $this->assertArrayHasKey('idiotikon', $providers);
        $this->assertArrayHasKey('metagrid', $providers);

        // Test that labels are arrays or strings and can be resolved
        $this->assertTrue(
            is_array($providers['idiotikon']['label']) || is_string($providers['idiotikon']['label']),
            'Label should be array or string'
        );
        $this->assertTrue(
            is_array($providers['metagrid']['label']) || is_string($providers['metagrid']['label']),
            'Label should be array or string'
        );

        // Test that LabelHelper can resolve the labels
        $this->assertIsString(\KraenzleRitter\ResourcesComponents\Helpers\LabelHelper::getProviderLabel('idiotikon'));
        $this->assertIsString(\KraenzleRitter\ResourcesComponents\Helpers\LabelHelper::getProviderLabel('metagrid'));
    }

    /**
     * Testet, dass die URL-Templates für alle Provider korrekt konfiguriert sind
     */
    public function test_url_templates_configuration()
    {
        // Überprüfen, ob alle relevanten Provider eine target_url haben oder korrekt verarbeitet werden können
        $providers = Config::get('resources-components.providers');

        // Die Provider, die target_url haben sollten
        $providersWithUrl = ['gnd', 'geonames', 'idiotikon', 'wikidata', 'ortsnamen'];

        foreach ($providersWithUrl as $provider) {
            $this->assertArrayHasKey($provider, $providers, "Provider {$provider} ist nicht konfiguriert");

            if (isset($providers[$provider])) {
                // Entweder sollte target_url existieren oder es gibt eine spezielle Fallback-Logik
                $hasTargetUrl = array_key_exists('target_url', $providers[$provider]);
                $this->assertTrue($hasTargetUrl, "Provider {$provider} hat keine target_url konfiguriert");

                // Wenn target_url existiert, sollte es den provider_id-Platzhalter enthalten
                if ($hasTargetUrl) {
                    $this->assertStringContainsString('{provider_id}', $providers[$provider]['target_url']);
                }
            }
        }
    }

    /**
     * Kombinierter Funktionstest für TestResourcesCommand
     */
    public function test_resources_command_integration()
    {
        // Test nur für die wichtigsten Provider
        $providers = ['wikipedia-de', 'idiotikon', 'metagrid'];

        foreach ($providers as $provider) {
            // Führen wir den Test-Befehl für jeden Provider aus
            $exitCode = Artisan::call('resources-components:test-resources', [
                '--provider' => $provider,
                '--no-cleanup' => true
            ]);

            // Befehl sollte erfolgreich ausgeführt werden
            $this->assertEquals(0, $exitCode, "Command für Provider {$provider} ist fehlgeschlagen");
        }

        // Wir müssen hier nicht aufräumen, da das im echten Command geschieht
        // Artisan::call('resources-components:test-resources');
    }

    /**
     * Testet die Struktur und Implementierung der Provider-Klassen
     */
    public function test_provider_classes_implementation()
    {
        // Überprüfen der Idiotikon-Klasse
        $idiotikon = new Idiotikon();
        $this->assertInstanceOf(Idiotikon::class, $idiotikon);
        $this->assertTrue(method_exists($idiotikon, 'search'), 'Idiotikon hat keine search-Methode');

        // Überprüfen der Metagrid-Klasse
        $metagrid = new Metagrid();
        $this->assertInstanceOf(Metagrid::class, $metagrid);
        $this->assertTrue(method_exists($metagrid, 'search'), 'Metagrid hat keine search-Methode');
        $this->assertTrue(method_exists($metagrid, 'getConcordance'), 'Metagrid hat keine getConcordance-Methode');
    }
}
