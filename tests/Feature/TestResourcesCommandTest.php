<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use KraenzleRitter\ResourcesComponents\Commands\TestResourcesCommand;
use KraenzleRitter\ResourcesComponents\Tests\TestCase;

class TestResourcesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testResourcesCommand()
    {
        // This will run the command in a testing environment
        $this->artisan('resources-components:test-resources')
             ->assertExitCode(0);
        
        // Check that resources were actually created
        $this->assertDatabaseHas('resources', [
            'provider' => 'wikipedia-de',
        ]);
    }
}
