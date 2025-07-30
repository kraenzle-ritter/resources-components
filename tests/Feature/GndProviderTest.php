<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class GndProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_gnd_resource_lifecycle()
    {
        // Testdaten für GND
        $provider = 'GND';
        $providerId = '118529579';  // Albert Einstein
        $url = 'https://lobid.org/gnd/118529579';
        $fullJson = json_encode([
            'gndIdentifier' => '118529579',
            'preferredName' => 'Einstein, Albert',
            'professionOrOccupation' => ['Physiker'],
            'dateOfBirth' => ['1879-03-14'],
            'dateOfDeath' => ['1955-04-18']
        ]);

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test GND Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => $fullJson
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die GND-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die GND-Resource wurde nicht erfolgreich entfernt");
    }
}
