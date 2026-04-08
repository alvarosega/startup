<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Customer\Checkout\CheckoutOrchestratorController;
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
use App\Http\Controllers\Web\Customer\Favorites\FavoriteController;
use App\Http\Controllers\Web\Customer\Brand\BrandController;
use App\Http\Controllers\Web\Customer\Featured\FeaturedController;
use App\Http\Controllers\Web\Customer\Sku\SkuController;


/*
|--------------------------------------------------------------------------
| Naming Note: Prefix 'customer.' is applied in bootstrap/app.php
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS Y DE CATÁLOGO ---

Route::get('/', ShopController::class)->name('index');
Route::get('/search', ShopController::class)->name('search');
Route::get('/zone/{zone:slug}', [ShopController::class, 'showZone'])->name('zone');
Route::get('/marcas/{slug}', [BrandController::class, 'show'])->name('brand.show');

Route::get('/product/{id}', ProductShowController::class)->name('product.show');
Route::get('/category/{category:slug}', CategoryController::class)->name('category');
Route::get('/bundle/{slug}', BundleController::class)->name('bundle');
Route::get('/sku/state', [SkuController::class, 'state'])->name('sku.state');

// Favoritos Index (Público para soporte de invitados)
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

Route::prefix('featured')->name('featured.')->group(function () {
    Route::get('/{product:slug}', [FeaturedController::class, 'show'])->name('show');
});

// --- GESTIÓN DE CARRITO (Público/Híbrido) ---
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'upsert'])->name('upsert');

    Route::post('/add-template', [CartController::class, 'addTemplate'])->name('add-template');

    Route::patch('/{id}', [CartController::class, 'update'])->name('update');
    Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
});

// --- AUTENTICACIÓN (Solo Invitados) ---
Route::middleware('guest:customer')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('register/validate-step-1', [RegisterController::class, 'validateStep1'])->name('register.validate-step-1');
    
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('request');
        Route::post('/email', [ForgotPasswordController::class, 'sendResetCode'])->name('email');
        Route::get('/reset/{email}', [ResetPasswordController::class, 'showResetForm'])->name('reset');
        Route::post('/update', [ResetPasswordController::class, 'reset'])->name('update');
    });
});

// --- RUTAS PROTEGIDAS (Solo Clientes Autenticados) ---
Route::middleware(['auth:customer'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Favoritos (Acciones)
    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::post('/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
        Route::post('/sync', [FavoriteController::class, 'sync'])->name('sync');
    });

    // Perfil y Direcciones
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        
        Route::prefix('addresses')->name('addresses.')->group(function () {
            Route::get('/', [AddressController::class, 'index'])->name('index');
            Route::get('/create', [AddressController::class, 'create'])->name('create');
            Route::post('/', [AddressController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AddressController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AddressController::class, 'update'])->name('update');
            Route::delete('/{id}', [AddressController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/default', [AddressController::class, 'makeDefault'])->name('set-default');
        });
    });

    Route::get('/checkout', [CheckoutOrchestratorController::class, 'index'])
    ->name('checkout.index');

    Route::post('/checkout', [CheckoutOrchestratorController::class, 'store'])
        ->name('checkout.store')
        ->middleware('idempotency');
/*
|--------------------------------------------------------------------------
| Silo de Pedidos del Cliente (Bajo el prefijo 'customer.')
|--------------------------------------------------------------------------
*/
Route::prefix('orders')->name('orders.')->group(function () {
    // RECTIFICACIÓN: El nombre debe ser 'index' para que route('customer.orders.index') funcione
    Route::get('/', [OrderController::class, 'index'])->name('index'); 
    
    // El 'show' actúa como el Enrutador de Estados (FSM)
    Route::get('/{id}', [OrderController::class, 'show'])->name('show');
    
    // Subida de comprobante
    Route::post('/{id}/proof', [OrderController::class, 'uploadProof'])->name('upload-proof');
});

    //Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});