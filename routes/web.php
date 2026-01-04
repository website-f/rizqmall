<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ProfileController; // Class name in ProfileDashboardController.php
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\VendorMemberController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (SSO from Sandbox System)
|--------------------------------------------------------------------------
*/

Route::get('/auth/sso', [AuthController::class, 'handleSsoLogin'])->name('auth.sso');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/verify-session', [AuthController::class, 'verifySession'])->name('auth.verify-session');
Route::get('/subscription/expired', [AuthController::class, 'subscriptionExpired'])->name('subscription.expired');

// Login routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Customer Registration routes
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('customer.register.form');
Route::post('/register', [AuthController::class, 'register'])->name('customer.register');



/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::get('/', [StoreController::class, 'home'])->name('rizqmall.home');
Route::get('/stores', [StoreController::class, 'stores'])->name('stores');
Route::get('/stores/{store:slug}', [StoreController::class, 'showProfile'])->name('store.profile');

// Vendor Member Routes (Public - for QR code scans)
Route::get('/join/{code}', [VendorMemberController::class, 'joinByQr'])->name('vendor.member.join.qr');

// Vendor Member Routes (Requires Auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/stores/{store}/join', [VendorMemberController::class, 'joinStore'])->name('vendor.member.join');
    Route::post('/member/join-by-code', [VendorMemberController::class, 'joinByCode'])->name('vendor.member.join.code');
    Route::get('/stores/{store}/membership-status', [VendorMemberController::class, 'getMembershipStatus'])->name('vendor.member.status');
    Route::get('/my-memberships', [VendorMemberController::class, 'getMyMemberships'])->name('vendor.member.my-memberships');
});

// Vendor Member Routes (For Store Owners)
Route::middleware(['auth', 'verify.vendor'])->group(function () {
    Route::get('/vendor/store/{store}/members', [VendorMemberController::class, 'getMembers'])->name('vendor.members.list');
    Route::get('/vendor/store/{store}/qr-code', [VendorMemberController::class, 'getQrCode'])->name('vendor.member.qr');
    Route::get('/vendor/store/{store}/ref-code', [VendorMemberController::class, 'getRefCode'])->name('vendor.member.ref-code');
});

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
| Vendor Routes (Store Setup & Management) - Auth Only (No Session Validation)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verify.vendor'])->group(function () {

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
        Route::get('/store/edit', [StoreController::class, 'edit'])->name('store.edit');
        Route::put('/store/update', [StoreController::class, 'update'])->name('store.update');
        Route::post('/store/{store}/change-banner', [StoreController::class, 'changeBanner'])->name('store.changeBanner');
        Route::post('/store/change-logo', [StoreController::class, 'changeLogo'])->name('store.changeLogo');

        // Product Management
        Route::resource('products', ProductController::class);
        Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');
        Route::delete('/products/{product}/images/{image}', [ProductController::class, 'deleteImage'])
            ->name('products.images.delete');

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

        // My Stores
        Route::get('/my-stores', [VendorDashboardController::class, 'myStores'])->name('my-stores');

        // Store Purchases
        Route::post('/store-purchase', [\App\Http\Controllers\StorePurchaseController::class, 'purchase'])->name('store-purchase.create');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'validate.session'])->group(function () {

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
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

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
        Route::post('/wishlist/add/{product}', [CustomerDashboardController::class, 'addToWishlist'])->name('wishlist.add');
        Route::delete('/wishlist/remove/{wishlist}', [CustomerDashboardController::class, 'removeFromWishlist'])->name('wishlist.remove');
        Route::post('/wishlist/add-all-to-cart', [CustomerDashboardController::class, 'addAllToCart'])->name('wishlist.add-all-to-cart');

        // Reviews
        Route::get('/reviews', [CustomerDashboardController::class, 'reviews'])->name('reviews');
        Route::post('/reviews', [CustomerDashboardController::class, 'storeReview'])->name('reviews.store');
        Route::post('/store-reviews', [CustomerDashboardController::class, 'storeStoreReview'])->name('reviews.store_store');
    });

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes (Authenticated Users Only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });
});

/*
|--------------------------------------------------------------------------
| Payment Callback Routes (Public - No Auth Required)
| ToyyibPay needs to call these endpoints directly
|--------------------------------------------------------------------------
*/
Route::post('/checkout/callback', [CheckoutController::class, 'callback'])->name('checkout.callback');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/store-purchase/callback', [\App\Http\Controllers\StorePurchaseController::class, 'callback'])->name('store-purchase.callback');

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

// Fallback route for storage files (Windows symlink workaround)
// MUST be at the end to avoid conflicts with other routes
Route::get('/storage/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);
    if (!file_exists($file)) {
        abort(404);
    }
    return response()->file($file);
})->where('path', '.*');
