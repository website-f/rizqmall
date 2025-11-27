<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Product variants API
Route::get('/products/{id}/variants', [ProductController::class, 'getVariants']);
