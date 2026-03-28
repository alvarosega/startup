<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Customer\Auth\LoginController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController;
use App\Http\Controllers\Web\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Customer\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Customer\Shop\ShopController;
use App\Http\Controllers\Web\Customer\Category\CategoryController;
use App\Http\Controllers\Web\Customer\Bundle\BundleController;
use App\Http\Controllers\Web\Customer\Product\ProductShowController;
use App\Http\Controllers\Web\Customer\Cart\CartController;
use App\Http\Controllers\Web\Customer\Profiles\ProfileController;
use App\Http\Controllers\Web\Customer\Profiles\AddressController;
use App\Http\Controllers\Web\Customer\Order\CheckoutController;
use App\Http\Controllers\Web\Customer\Order\OrderController;
use App\Http\Controllers\Web\Customer\Catalog\FavoriteController;
use App\Http\Controllers\Web\Customer\Catalog\ReviewController;

Route::get('/product/{id}', ProductShowController::class)->name('product.show');

Route::name('shop.')->group(function () {
    // Cambiamos ShopLandingController por ShopIndexController
    Route::get('/', ShopController::class)->name('index');
    Route::get('/search', [ShopController::class, 'index'])->name('search');
    Route::get('/zone/{zone:slug}', [ShopController::class, 'showZone'])->name('zone');
    // Mantenemos 'category' como nombre del parámetro para consistencia con el Resource
    Route::get('/category/{category:slug}', CategoryController::class)->name('category');
    Route::get('/bundle/{slug}', BundleController::class)->name('bundle');
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'upsert'])->name('upsert');
    Route::patch('/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
});

Route::middleware('guest:customer')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('register/validate-step-1', [RegisterController::class, 'validateStep1'])->name('register.validate-step-1');
    Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
    Route::get('password/reset/{email}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/update', [ResetPasswordController::class, 'reset'])->name('password.update');
    Route::get('/geo/validate', [App\Http\Controllers\Api\Geo\GeoController::class, 'validatePoint'])
    ->name('api.geo.validate');
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

// Rutas protegidas del cliente
Route::middleware(['auth:customer'])->prefix('customer')->group(function () {
    
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses'); // <--- Puntero corregido
        Route::get('/security', [ProfileController::class, 'security'])->name('security');

        Route::prefix('addresses')->name('addresses.')->group(function () {
            // NUEVAS RUTAS PARA LA VISTA DEDICADA
            Route::get('/create', [AddressController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [AddressController::class, 'edit'])->name('edit');
            
            // Rutas de acción (Ya las tenías)
            Route::post('/', [AddressController::class, 'store'])->name('store');
            Route::put('/{id}', [AddressController::class, 'update'])->name('update');
            Route::delete('/{id}', [AddressController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/default', [AddressController::class, 'makeDefault'])->name('set-default');
        });
    });

    Route::post('/favorites/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('history');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/proof', [OrderController::class, 'uploadProof'])->name('upload-proof');
        Route::get('/{id}/track', [OrderController::class, 'track'])->name('track');
        Route::get('/{id}/telemetry', [OrderController::class, 'getTelemetry'])->name('telemetry');
    });
});