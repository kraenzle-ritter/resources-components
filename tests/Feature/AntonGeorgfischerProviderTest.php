<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class AntonGeorgfischerProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_anton_georgfischer_resource_lifecycle()
    {
        // Testdaten für Anton mit Georgfischer
        $provider = 'Georgfischer';
        $providerId = '12345';
        $baseUrl = 'https://anton.ch/'; // Beispiel-Basis-URL
        $url = $baseUrl . 'objects/' . $providerId;

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test Anton Georgfischer Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => json_encode(['id' => $providerId, 'name' => 'Georg Fischer Testobjekt'])
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die Anton-Georgfischer-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die Anton-Georgfischer-Resource wurde nicht erfolgreich entfernt");
    }
}
