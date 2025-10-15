<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorStoreController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\ProfileController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (SSO from Subscription System)
|--------------------------------------------------------------------------
*/
Route::get('/auth/redirect', [AuthController::class, 'handleSubscriptionRedirect'])->name('auth.redirect');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/verify-session', [AuthController::class, 'verifySession'])->name('auth.verify-session');
Route::get('/subscription/expired', [AuthController::class, 'subscriptionExpired'])->name('subscription.expired');
Route::get('/subscription/renew', [AuthController::class, 'redirectToRenewal'])->name('subscription.renew');

// Guest checkout
Route::post('/guest-checkout', [AuthController::class, 'guestCheckout'])->name('guest.checkout');

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::get('/', [StoreController::class, 'home'])->name('rizqmall.home');
Route::get('/stores', [StoreController::class, 'stores'])->name('stores');
Route::get('/stores/{store:slug}', [StoreController::class, 'showProfile'])->name('store.profile');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/services', [ProductController::class, 'index'])->defaults('type', 'service')->name('services.index');
Route::get('/pharmacy', [ProductController::class, 'index'])->defaults('type', 'pharmacy')->name('pharmacy.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| Cart Routes (Session-based, works for guests and logged-in users)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'validate.session'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Vendor Routes (Store Setup & Management)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['verify.vendor', 'check.subscription'])->group(function () {
        
        // Store Setup Flow (First Time)
        Route::get('/select-store-category', [StoreController::class, 'showCategorySelection'])
            ->name('store.select-category');
        Route::get('/setup-store', [StoreController::class, 'showSetupForm'])
            ->name('store.setup');
        Route::post('/setup-store', [StoreController::class, 'store'])
            ->name('store.store');

        // Vendor Dashboard
        Route::prefix('vendor')->name('vendor.')->group(function () {
            Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
            
            // Store Management
            Route::get('/store/edit', [VendorStoreController::class, 'edit'])->name('store.edit');
            Route::put('/store/update', [VendorStoreController::class, 'update'])->name('store.update');
            Route::post('/store/change-banner', [VendorStoreController::class, 'changeBanner'])->name('store.changeBanner');
            Route::post('/store/change-logo', [VendorStoreController::class, 'changeLogo'])->name('store.changeLogo');
            
            // Product Management
            Route::resource('products', VendorProductController::class);
            Route::post('/products/{product}/toggle-status', [VendorProductController::class, 'toggleStatus'])
                ->name('products.toggle-status');
            
            // Orders
            Route::get('/orders', [VendorDashboardController::class, 'orders'])->name('orders.index');
            Route::get('/orders/{order}', [VendorDashboardController::class, 'showOrder'])->name('orders.show');
            Route::put('/orders/{order}/status', [VendorDashboardController::class, 'updateOrderStatus'])
                ->name('orders.update-status');
            
            // Analytics
            Route::get('/analytics', [VendorDashboardController::class, 'analytics'])->name('analytics');
            
            // Settings
            Route::get('/settings', [VendorDashboardController::class, 'settings'])->name('settings');
            Route::put('/settings', [VendorDashboardController::class, 'updateSettings'])->name('settings.update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Routes (Profile & Orders)
    |--------------------------------------------------------------------------
    */
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        
        // Addresses
        Route::get('/addresses', [ProfileController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [ProfileController::class, 'deleteAddress'])->name('addresses.delete');
        Route::post('/addresses/{address}/set-default', [ProfileController::class, 'setDefaultAddress'])
            ->name('addresses.set-default');
        
        // Orders
        Route::get('/orders', [CustomerDashboardController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{order}', [CustomerDashboardController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [CustomerDashboardController::class, 'cancelOrder'])
            ->name('orders.cancel');
        
        // Wishlist
        Route::get('/wishlist', [CustomerDashboardController::class, 'wishlist'])->name('wishlist');
        
        // Reviews
        Route::get('/reviews', [CustomerDashboardController::class, 'reviews'])->name('reviews');
    });

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes (Authenticated Users Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('process');
        Route::get('/success/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('success');
    });
});

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
*/
Route::post('/uploads/temp', [UploadController::class, 'store'])->name('uploads.temp');

/*
|--------------------------------------------------------------------------
| Legacy Product Routes (For backwards compatibility)
|--------------------------------------------------------------------------
| These maintain compatibility with old store setup flow
*/
Route::middleware(['auth', 'verify.vendor', 'verify.store.owner'])->group(function () {
    Route::prefix('store/{store}')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Route Patterns
|--------------------------------------------------------------------------
*/
Route::pattern('store', '[0-9]+');
Route::pattern('product', '[0-9]+');
Route::pattern('slug', '[a-z0-9-]+');