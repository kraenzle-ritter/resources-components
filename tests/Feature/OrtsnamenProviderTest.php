<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class OrtsnamenProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_ortsnamen_resource_lifecycle()
    {
        // Testdaten für Ortsnamen.ch
        $provider = 'Ortsnamen';
        $providerId = 'zuerich-123';
        $url = 'https://ortsnamen.ch/de/detail/zuerich-123';
        $fullJson = json_encode([
            'id' => 'zuerich-123',
            'name' => 'Zürich',
            'permalink' => 'https://ortsnamen.ch/de/detail/zuerich-123',
            'types' => ['settlement', 'city'],
            'description' => ['Stadt und Hauptort des Kantons Zürich']
        ]);

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test Ortsnamen Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => $fullJson
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die Ortsnamen-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die Ortsnamen-Resource wurde nicht erfolgreich entfernt");
    }
}
