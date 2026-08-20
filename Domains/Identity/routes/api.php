<?php

use Domains\Identity\Http\Controllers\ApiKeyController;
use Domains\Identity\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::controller(ApiKeyController::class)->prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('me', 'get')->name('me');
    });
});
