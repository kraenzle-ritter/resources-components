<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class GeonamesProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_geonames_resource_lifecycle()
    {
        // Testdaten für Geonames
        $provider = 'Geonames';
        $providerId = '2657896';  // Zürich
        $url = 'https://www.geonames.org/2657896';
        $fullJson = json_encode([
            'geonameId' => 2657896,
            'toponymName' => 'Zürich',
            'countryName' => 'Switzerland',
            'lat' => 47.36667,
            'lng' => 8.55,
            'fclName' => 'city, village'
        ]);

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test Geonames Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => $fullJson
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die Geonames-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die Geonames-Resource wurde nicht erfolgreich entfernt");
    }
}
