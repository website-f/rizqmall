<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProductController;

Route::get('/setup-store', [StoreController::class, 'showSetupForm'])->name('store.setup');
Route::get('/stores', [StoreController::class, 'stores'])->name('stores');
Route::get('/view-store', [StoreController::class, 'viewStore'])->name('view.store');
Route::get('/stores/{store:slug}', [StoreController::class, 'showProfile'])->name('store.profile');
Route::post('/store/{store}/change-banner', [StoreController::class, 'changeBanner'])
    ->name('store.changeBanner');
Route::post('/setup-store', [StoreController::class, 'store'])->name('store.store');
Route::get('/store/{store}/products', [ProductController::class, 'showProductsForm'])->name('store.products');
Route::post('/store/{store}/products', [ProductController::class, 'store'])->name('store.products.store');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/products', [ProductController::class, 'index'])->name('product.index');
Route::get('/', [StoreController::class, 'home'])->name('rizqmall.home');

// routes/web.php
Route::post('/uploads/temp', [UploadController::class, 'store'])->name('uploads.temp');
