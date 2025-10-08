<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Homepage
|--------------------------------------------------------------------------
*/
Route::get('/', [StoreController::class, 'home'])->name('rizqmall.home');

/*
|--------------------------------------------------------------------------
| Store Setup Flow
|--------------------------------------------------------------------------
*/
// Step 1: Select Store Category
Route::get('/select-store-category', [StoreController::class, 'showCategorySelection'])
    ->name('store.select-category');

// Step 2: Setup Store Details
Route::get('/setup-store', [StoreController::class, 'showSetupForm'])
    ->name('store.setup');

// Step 3: Create Store
Route::post('/setup-store', [StoreController::class, 'store'])
    ->name('store.store');

/*
|--------------------------------------------------------------------------
| Store Routes
|--------------------------------------------------------------------------
*/
Route::get('/stores', [StoreController::class, 'stores'])->name('stores');
Route::get('/stores/{store:slug}', [StoreController::class, 'showProfile'])->name('store.profile');
Route::post('/store/{store}/change-banner', [StoreController::class, 'changeBanner'])->name('store.changeBanner');

/*
|--------------------------------------------------------------------------
| Product Management (Store Owner)
|--------------------------------------------------------------------------
*/
Route::prefix('store/{store}')->group(function () {
    // Create Product/Service/Pharmacy
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    
    // Edit & Update
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    
    // Delete
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/count', [CartController::class, 'count'])->name('cart.count');
});

/*
|--------------------------------------------------------------------------
| Public Product Routes (Customer Facing)
|--------------------------------------------------------------------------
*/
// Product Listing
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/services', [ProductController::class, 'index'])->defaults('type', 'service')->name('services.index');
Route::get('/pharmacy', [ProductController::class, 'index'])->defaults('type', 'pharmacy')->name('pharmacy.index');

// Product Detail
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
*/
Route::post('/uploads/temp', [UploadController::class, 'store'])->name('uploads.temp');

/*
|--------------------------------------------------------------------------
| Route Patterns
|--------------------------------------------------------------------------
*/
Route::pattern('store', '[0-9]+');
Route::pattern('product', '[0-9]+');
Route::pattern('slug', '[a-z0-9-]+');