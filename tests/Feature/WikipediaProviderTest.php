<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class WikipediaProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_wikipedia_resource_lifecycle()
    {
        // Testdaten für Wikipedia
        $provider = 'Wikipedia';
        $providerId = '12345';
        $url = 'https://de.wikipedia.org/wiki/Albert_Einstein';

        // 1. Modell erstellen
        $model = DummyModel::create(['name' => 'Test Wikipedia Model']);

        // 2. Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => $providerId,
            'url' => $url
        ]);

        // 3. Überprüfen, ob die Resource vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', $providerId),
            "Die Wikipedia-Resource wurde nicht erfolgreich hinzugefügt");
        $this->assertTrue($model->resources->contains('provider', $provider),
            "Die Resource hat nicht den korrekten Provider");
        $this->assertTrue($model->resources->contains('url', $url),
            "Die Resource hat nicht die korrekte URL");

        // 4. Resource löschen
        $model->removeResource($providerId);

        // 5. Überprüfen, ob die Resource entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', $providerId),
            "Die Wikipedia-Resource wurde nicht erfolgreich entfernt");
    }
}
