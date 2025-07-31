<?php

use Illuminate\Support\Facades\Route;
use KraenzleRitter\ResourcesComponents\Http\Controllers\ResourcesCheckController;

Route::group(['middleware' => ['web'], 'as' => 'resources.check.'], function () {
    Route::get('/resources-check', [ResourcesCheckController::class, 'index'])->name('index');
    Route::get('/resources-check/run-all', [ResourcesCheckController::class, 'index'])->name('run-all-tests');
    Route::get('/resources-check/provider/{provider}', [ResourcesCheckController::class, 'showProvider'])->name('provider');
    Route::post('/resources-check/provider/{provider}/test', [ResourcesCheckController::class, 'showProvider'])->name('test-provider');
    Route::get('/resources-check/config', [ResourcesCheckController::class, 'showConfig'])->name('show-config');
});
