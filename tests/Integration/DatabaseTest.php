<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Integration;

use KraenzleRitter\ResourcesComponents\Tests\TestCase;
use KraenzleRitter\ResourcesComponents\Tests\Support\DummyModel;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTestDatabase();
    }

    public function test_model_is_saved_to_database()
    {
        $model = DummyModel::create(['name' => 'Test']);

        $this->assertDatabaseHas('dummy_models', [
            'name' => 'Test',
        ]);
    }
}
