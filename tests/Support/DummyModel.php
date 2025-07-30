<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use KraenzleRitter\Resources\HasResources;

class DummyModel extends Model
{
    use HasResources;

    protected $table = 'dummy_models';
    protected $guarded = [];
    public $resources;
    public $timestamps = true; // Wir aktivieren explizit die Zeitstempel

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->resources = collect([]);
    }

    public function load($relation)
    {
        // Simuliert das Eloquent-Load-Verhalten
        return $this;
    }

    /**
     * Eine manuelle Implementierung von updateOrCreateResource für die Tests
     */
    public function updateOrCreateResource($data)
    {
        // Erstelle eine neue Resource
        $resource = [
            'id' => rand(1000, 9999),
            'provider' => $data['provider'],
            'provider_id' => $data['provider_id'],
            'url' => $data['url']
        ];

        // Füge sie zur Collection hinzu
        $this->resources->push($resource);

        return $resource;
    }

    /**
     * Eine manuelle Implementierung von removeResource für die Tests
     */
    public function removeResource($resourceId)
    {
        $this->resources = $this->resources->filter(function($item) use ($resourceId) {
            return $item['provider_id'] != $resourceId;
        });
    }
}
