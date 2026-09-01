<?php

namespace Domains\Shared\Routing;

use Domains\Shared\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

/**
 * Registers the shared category CRUD routes for one app. Call once per app
 * from inside that domain's own `Route::prefix('v1/{app}')
 * ->middleware(['auth:sanctum', 'app:{app}'])->group(...)` wrapper — that
 * wrapper already supplies the app prefix and access-gate middleware, so this
 * only needs to add the `categories` sub-group and inject `app_code` as a
 * route default for the shared controller/repository to scope on.
 */
class CategoryRoutes
{
    public static function register(string $appCode): void
    {
        Route::controller(CategoryController::class)
            ->prefix('categories')
            ->name("{$appCode}.categories.")
            ->group(function () use ($appCode) {
                Route::get('/', 'list')->name('list')->defaults('app_code', $appCode);
                Route::post('/', 'create')->name('create')->defaults('app_code', $appCode);
                Route::get('/{id}', 'get')->name('get')->defaults('app_code', $appCode);
                Route::put('/{id}', 'update')->name('update')->defaults('app_code', $appCode);
                Route::delete('/{id}', 'delete')->name('delete')->defaults('app_code', $appCode);
            });
    }
}
