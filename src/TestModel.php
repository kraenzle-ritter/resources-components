<?php

namespace KraenzleRitter\ResourcesComponents;

use Illuminate\Database\Eloquent\Model;
use KraenzleRitter\Resources\HasResources;

class TestModel extends Model
{
    use HasResources;
    
    protected $table = "test_models";
    protected $fillable = ["name"];
    public $timestamps = false;
}
