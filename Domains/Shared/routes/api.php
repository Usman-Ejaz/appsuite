<?php

use Domains\Shared\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::controller(SiteController::class)->prefix('sites')->name('sites.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });
});
