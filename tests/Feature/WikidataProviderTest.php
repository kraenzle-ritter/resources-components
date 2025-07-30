<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class WikidataProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_wikidata_resource_lifecycle()
    {
        // Testdaten für Wikidata
        $provider = 'Wikidata';
        $providerId = 'Q937';  // Albert Einstein
        $url = 'https://www.wikidata.org/wiki/Q937';
        $fullJson = json_encode([
            'id' => 'Q937',
            'label' => 'Albert Einstein',
            'description' => 'theoretischer Physiker'
        ]);

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test Wikidata Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url,
            'full_json' => $fullJson
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die Wikidata-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die Wikidata-Resource wurde nicht erfolgreich entfernt");
    }
}
