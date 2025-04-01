<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/{cartId}', 'get')->name('get');
    Route::post('/', 'addItem')->name('create-and-add-item');
    Route::post('/{cartId}', 'addItem')->name('add-item');
});
