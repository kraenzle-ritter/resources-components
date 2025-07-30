<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class MetagridProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_metagrid_resource_lifecycle()
    {
        // Testdaten für Metagrid
        $provider = 'Metagrid';
        $providerId = '12345';
        $url = 'https://metagrid.ch/12345';
        $fullJson = json_encode([
            'id' => '12345',
            'provider' => [
                'slug' => 'metagrid',
                'name' => 'Metagrid',
            ],
            'resources' => [
                [
                    'provider' => [
                        'slug' => 'hls',
                        'name' => 'Historisches Lexikon der Schweiz'
                    ],
                    'identifier' => 'hls-123',
                    'link' => [
                        'uri' => 'https://hls-dhs-dss.ch/123'
                    ]
                ]
            ]
        ]);

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test Metagrid Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => $fullJson
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die Metagrid-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die Metagrid-Resource wurde nicht erfolgreich entfernt");
    }
}
