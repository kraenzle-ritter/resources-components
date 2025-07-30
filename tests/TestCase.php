<?php


namespace KraenzleRitter\ResourcesComponents\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use KraenzleRitter\ResourcesComponents\ResourcesComponentsServiceProvider;

abstract class TestCase extends Orchestra
{

    /**
     * Erstellt die Anwendungsinstanz.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        // Stellt sicher, dass Artisan korrekt initialisiert wird
        $app->make('Illuminate\Contracts\Console\Kernel');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * Bereinigt die Datenbank und setzt sie zurück
     */
    protected function refreshTestDatabase()
    {
        // Bei In-Memory-Datenbank reicht es, die Tabellen zu droppen und neu zu erstellen
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        // Bestehende Tabellen droppen
        $schema->dropIfExists('resources');
        $schema->dropIfExists('dummy_models');

        // Tabellen neu erstellen
        $schema->create('dummy_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps(); // Fügt created_at und updated_at hinzu
        });

        // Resources Tabelle erstellen
        (require __DIR__.'/migrations/create_resources_table.php')->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            ResourcesComponentsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->initializeDirectory($this->getTempDirectory());
        $dbPath = $this->getTempDirectory() . '/database.sqlite';

        // SQLite im Memory-Modus verwenden, um Berechtigungsprobleme zu vermeiden
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // View-Namespace für Komponenten
        $app['view']->addNamespace(
            'resources-components',
            base_path('packages/kraenzle-ritter/resources-components/resources/views')
        );

        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }

    protected function setUpDatabase(Application $app)
    {
        // Bei In-Memory-Datenbank nur Tabellen erstellen
        $schema = $app['db']->connection()->getSchemaBuilder();

        // Tabellen erstellen
        $schema->create('dummy_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps(); // Fügt created_at und updated_at hinzu
        });

        // Resources Tabelle erstellen
        (require __DIR__.'/migrations/create_resources_table.php')->up();
    }

    protected function initializeDirectory(string $directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    public function getTempDirectory(): string
    {
        return __DIR__ . '/temp';
    }
}
