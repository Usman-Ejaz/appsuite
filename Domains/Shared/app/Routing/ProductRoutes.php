<?php

namespace Domains\Shared\Routing;

use Domains\Shared\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/**
 * Registers the shared product CRUD routes for one app. Same shape as
 * CategoryRoutes::register() — call once per app from inside that domain's
 * own route group.
 */
class ProductRoutes
{
    public static function register(string $appCode): void
    {
        Route::controller(ProductController::class)
            ->prefix('products')
            ->name("{$appCode}.products.")
            ->group(function () use ($appCode) {
                Route::get('/', 'list')->name('list')->defaults('app_code', $appCode);
                Route::post('/', 'create')->name('create')->defaults('app_code', $appCode);
                Route::get('/{id}', 'get')->name('get')->defaults('app_code', $appCode);
                Route::put('/{id}', 'update')->name('update')->defaults('app_code', $appCode);
                Route::delete('/{id}', 'delete')->name('delete')->defaults('app_code', $appCode);
            });
    }
}
