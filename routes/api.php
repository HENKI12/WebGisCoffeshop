<?php

use App\Http\Controllers\CoffeeShopController;
use Illuminate\Support\Facades\Route;

Route::get('/public/coffee-shops', [CoffeeShopController::class, 'publicIndex']);

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/coffee-shops', [CoffeeShopController::class, 'index']);
    Route::post('/coffee-shops', [CoffeeShopController::class, 'store']);
    Route::get('/coffee-shops/{coffeeShop}', [CoffeeShopController::class, 'show']);
    Route::put('/coffee-shops/{coffeeShop}', [CoffeeShopController::class, 'update']);
    Route::delete('/coffee-shops/{coffeeShop}', [CoffeeShopController::class, 'destroy']);
});
