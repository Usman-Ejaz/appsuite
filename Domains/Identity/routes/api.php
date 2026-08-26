<?php

use Domains\Identity\Http\Controllers\ApiKeyController;
use Domains\Identity\Http\Controllers\CompanyController;
use Domains\Identity\Http\Controllers\PermissionController;
use Domains\Identity\Http\Controllers\ProfileController;
use Domains\Identity\Http\Controllers\RoleController;
use Domains\Identity\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::controller(ApiKeyController::class)->prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(CompanyController::class)->prefix('companies')->name('identity.companies.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(UserController::class)->prefix('users')->name('identity.users.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(RoleController::class)->prefix('roles')->name('identity.roles.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(PermissionController::class)->prefix('permissions')->name('identity.permissions.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/{id}', 'get')->name('get');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('me', 'get')->name('me');
    });
});
