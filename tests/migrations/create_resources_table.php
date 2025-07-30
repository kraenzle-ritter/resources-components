<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_id');
            $table->string('url');
            $table->json('full_json')->nullable();
            $table->morphs('resourceable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('resources');
    }
};
