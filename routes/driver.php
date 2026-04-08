<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Driver\Auth\LoginController;
use App\Http\Controllers\Web\Driver\Auth\RegisterController;
use App\Http\Controllers\Web\Driver\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Driver\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Driver\DashboardController;
use App\Http\Controllers\Web\Driver\Profile\DriverProfileController;
use App\Http\Controllers\Web\Driver\Order\OrderController;

Route::middleware('guest:driver')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register'); 
    Route::post('register/validate-step-1', [RegisterController::class, 'validateStep1'])->name('register.validate-step-1');
    Route::post('register/store', [RegisterController::class, 'store'])->name('register.store');
    
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    
    Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
    Route::get('password/reset/{email}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/update', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth:driver'])->group(function () {
    // 1. EL CEREBRO (SMART ROUTER)
    // Esta es la ruta que Ziggy no encontraba.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Estado y Perfil
    Route::post('/status/toggle', [DashboardController::class, 'toggleStatus'])->name('status.toggle');
    Route::get('/profile', [DriverProfileController::class, 'index'])->name('profile.index');
    Route::post('/upload-docs', [DriverProfileController::class, 'uploadDocs'])->name('upload-docs');
    
    // 3. Módulo de Órdenes (Ciclo de Vida)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:code}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:code}/take', [OrderController::class, 'take'])->name('orders.take');
    Route::post('/orders/{order:code}/verify-pickup', [OrderController::class, 'verifyPickup'])->name('orders.verify-pickup');
    
    // Rutas de Entrega Final (Inyectadas para Delivery.vue)
    Route::post('/orders/{order:code}/arrived', [OrderController::class, 'markArrived'])->name('orders.arrived');
    Route::post('/orders/{order:code}/deliver', [OrderController::class, 'deliver'])->name('orders.deliver');
    
    // 4. Historial y Salida
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout'); 
});