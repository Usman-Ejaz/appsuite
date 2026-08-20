<?php

use Domains\Auth\Http\Controllers\LoginController;
use Domains\Auth\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post('login', 'login')->middleware('throttle:login')->name('login');
        Route::post('logout', 'logout')->middleware('auth:sanctum')->name('logout');
    });

    Route::controller(TokenController::class)->group(function () {
        Route::post('token', 'create')->name('token');
    });
});
