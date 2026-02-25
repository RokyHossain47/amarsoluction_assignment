<?php

use App\Http\Controllers\ProductController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/api/products', [ProductController::class, 'products']);
// Route::post('/api/add-product', [ProductController::class, 'addProduct'])->middleware('token');
