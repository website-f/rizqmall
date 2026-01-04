<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Product variants API
Route::get('/products/{id}/variants', [ProductController::class, 'getVariants']);

// Sandbox API integration
Route::middleware(['api'])->prefix('sandbox')->group(function () {
    // User creation from Sandbox (when user registers in Sandbox)
    Route::post('/create-user', [\App\Http\Controllers\Api\UserApiController::class, 'createFromSandbox']);

    // Find user by email (for linking existing accounts)
    Route::get('/user-by-email', [\App\Http\Controllers\Api\UserApiController::class, 'findByEmail']);

    // Link user to Sandbox
    Route::post('/link-user', [\App\Http\Controllers\Api\UserApiController::class, 'linkToSandbox']);
});
