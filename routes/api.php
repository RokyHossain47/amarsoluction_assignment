<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Request;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'getProducts']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/add-products', [ProductController::class, 'addProducts']);
    Route::post('/update-products', [ProductController::class, 'updateProducts']);
    Route::get('/product-delete', [ProductController::class, 'deleteProducts']);
    
    // orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

});
