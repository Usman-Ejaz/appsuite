<?php

use Domains\CMS\Http\Controllers\BlogController;
use Domains\CMS\Http\Controllers\FormActionController;
use Domains\CMS\Http\Controllers\FormController;
use Domains\CMS\Http\Controllers\FormFieldController;
use Domains\CMS\Http\Controllers\FormSubmissionController;
use Domains\Shared\Routing\CategoryRoutes;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/cms')->middleware(['auth:sanctum', 'app:cms'])->name('cms.')->group(function () {

    // CategoryRoutes::registerFor('cms');

    Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(BlogController::class)->prefix('blogs')->name('blogs.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
        Route::post('/{id}/publish', 'publish')->name('publish');
        Route::post('/{id}/unpublish', 'unpublish')->name('unpublish');
    });

    Route::controller(FormController::class)->prefix('forms')->name('forms.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{form}', 'get')->name('get');
        Route::put('/{form}', 'update')->name('update');
        Route::delete('/{form}', 'delete')->name('delete');
        Route::post('/{form}/submit', 'submit')->name('submit');
    });

    Route::controller(FormFieldController::class)->prefix('forms/{form_id}/fields')->name('forms.fields.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::patch('/reorder', 'reorder')->name('reorder');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(FormActionController::class)->prefix('forms/{form_id}/actions')->name('forms.actions.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(FormSubmissionController::class)->prefix('forms/{form_id}/submissions')->name('forms.submissions.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });
});
