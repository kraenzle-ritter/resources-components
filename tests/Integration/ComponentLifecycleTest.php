<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Integration;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class ComponentLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_model_creation_and_resource_addition()
    {
        $provider = 'Wikipedia';
        $searchResult = [
            'provider' => $provider,
            'provider_id' => 12345,
            'url' => 'https://wikipedia.org/wiki/Test_Resource',
        ];

        $model = DummyModel::create(['name' => 'Test Model']);
        $model->updateOrCreateResource($searchResult);

        $this->assertTrue($model->resources->contains('provider_id', 12345),
            "Failed asserting that the model contains a resource with provider_id 12345");
    }

    public function test_resource_existence_in_list()
    {
        $provider = 'Wikipedia';
        $model = DummyModel::create(['name' => 'Test Model']);
        $model->resources = collect([
            ['provider' => $provider, 'provider_id' => 12345],
        ]);

        $this->assertTrue($model->resources->contains('provider', $provider));
    }

    public function test_resource_deletion()
    {
        $provider = 'Wikipedia';
        $model = DummyModel::create(['name' => 'Test Model']);

        // Direkt die Resource hinzufügen
        $model->updateOrCreateResource([
            'provider' => $provider,
            'provider_id' => 12345,
            'url' => 'https://wikipedia.org/wiki/Test_Resource'
        ]);

        // Überprüfen, dass sie vorhanden ist
        $this->assertTrue($model->resources->contains('provider_id', 12345),
            "Fehler beim Einfügen der Resource vor dem Löschen");

        // Resource entfernen
        $model->removeResource(12345);

        // Überprüfen, dass sie entfernt wurde
        $this->assertFalse($model->resources->contains('provider_id', 12345),
            "Failed asserting that the model does not contain a resource with provider_id 12345");
    }
}
