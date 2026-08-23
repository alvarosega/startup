<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Customer\Auth\LoginController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController;
use App\Http\Controllers\Web\Customer\Auth\LogoutController;
use App\Http\Controllers\Web\Customer\Shop\ShopController;
use App\Http\Controllers\Web\Customer\Category\CategoryController;
use App\Http\Controllers\Web\Customer\Profiles\ProfileController;
use App\Http\Controllers\Web\Customer\Profiles\AddressController;

// --- ACCESO PÚBLICO (Invitados y Autenticados) ---
// URL: / -> Name: customer.index
Route::get('/', ShopController::class)->name('index');

// URL: /category/{category} -> Name: customer.category
Route::get('category/{category}', CategoryController::class)->name('category');


// --- PERÍMETRO EXCLUSIVO PARA INVITADOS ---
Route::middleware(['guest:customer'])->group(function (): void {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');
});


// --- PERÍMETRO PROTEGIDO ---
Route::middleware(['auth:customer'])->group(function (): void {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::prefix('profile')->name('profile.')->group(function (): void {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');

        Route::prefix('addresses')->name('addresses.')->group(function (): void {
            Route::get('/', [AddressController::class, 'index'])->name('index');
            Route::get('/create', [AddressController::class, 'create'])->name('create');
            Route::post('/', [AddressController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AddressController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AddressController::class, 'update'])->name('update');
            Route::post('/{id}/delete', [AddressController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/default', [AddressController::class, 'makeDefault'])->name('set-default');
        });
    });
});


// --- STUBS TEMPORALES PARA CONSONANCIA DE INTERFAZ (ZIGGY) ---
Route::get('cart-stub', fn (): string => '')->name('cart.index');
Route::get('order-stub', fn (): string => '')->name('order.index');
Route::get('password-reset-stub', fn (): string => '')->name('password.request');