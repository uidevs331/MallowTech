<?php

use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\LowStockProductController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/customers/{email}/orders', [CustomerOrderController::class, 'index'])
    ->where('email', '.+');
Route::get('/products/low-stock', [LowStockProductController::class, 'index']);
