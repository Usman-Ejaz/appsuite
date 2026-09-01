<?php

use Domains\Core\Enums\AppCode;
use Domains\Ecommerce\Http\Controllers\BrandController;
use Domains\Ecommerce\Http\Controllers\CampaignController;
use Domains\Ecommerce\Http\Controllers\CollectionController;
use Domains\Ecommerce\Http\Controllers\CouponController;
use Domains\Ecommerce\Http\Controllers\CustomerController;
use Domains\Ecommerce\Http\Controllers\OrderController;
use Domains\Ecommerce\Http\Controllers\OrderItemController;
use Domains\Ecommerce\Http\Controllers\PaymentMethodController;
use Domains\Ecommerce\Http\Controllers\ProductController;
use Domains\Ecommerce\Http\Controllers\ReviewController;
use Domains\Shared\Routing\CategoryRoutes;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/ecommerce')->middleware(['auth:sanctum', 'app:ecommerce'])->group(function () {

    $appCode = AppCode::ECOMMERCE->value;

    CategoryRoutes::register('ecommerce');

    Route::controller(ProductController::class)->prefix('products')->name("{$appCode}.products.")->group(function () use ($appCode) {
        Route::get('/', 'list')->name('list')->defaults('app_code', $appCode);
        Route::post('/', 'create')->name('create')->defaults('app_code', $appCode);
        Route::get('/{id}', 'get')->name('get')->defaults('app_code', $appCode);
        Route::put('/{id}', 'update')->name('update')->defaults('app_code', $appCode);
        Route::delete('/{id}', 'delete')->name('delete')->defaults('app_code', $appCode);
    });

    Route::controller(BrandController::class)->prefix('brands')->name("$appCode.brands.")->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(CollectionController::class)->prefix('collections')->name('ecommerce.collections.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
        Route::put('/{id}/products', 'syncProducts')->name('sync-products');
    });

    Route::controller(ReviewController::class)->prefix('reviews')->name('ecommerce.reviews.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
        Route::patch('/{id}/moderate', 'moderate')->name('moderate');
    });

    Route::controller(CampaignController::class)->prefix('campaigns')->name('ecommerce.campaigns.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(CouponController::class)->prefix('coupons')->name('ecommerce.coupons.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(PaymentMethodController::class)->prefix('payment-methods')->name('ecommerce.payment-methods.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(CustomerController::class)->prefix('customers')->name('ecommerce.customers.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    Route::controller(OrderController::class)->prefix('orders')->name('ecommerce.orders.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
        Route::post('/{id}/cancel', 'cancel')->name('cancel');
        Route::post('/{id}/apply-coupon', 'applyCoupon')->name('apply-coupon');
        Route::post('/{id}/remove-coupon', 'removeCoupon')->name('remove-coupon');
    });

    Route::controller(OrderItemController::class)->prefix('orders/{order_id}/items')->name('ecommerce.orders.items.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'get')->name('get');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });
});
