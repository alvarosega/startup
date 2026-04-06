<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Driver\Auth\LoginController;
use App\Http\Controllers\Web\Driver\Auth\RegisterController;
use App\Http\Controllers\Web\Driver\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Driver\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Driver\DashboardController;
use App\Http\Controllers\Web\Driver\Profile\DriverProfileController;

Route::middleware('guest:driver')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register'); 
    Route::post('register/validate-step-1', [RegisterController::class, 'validateStep1'])->name('register.validate-step-1');
    Route::post('register/store', [RegisterController::class, 'store'])->name('register.store');
    
    // NUEVA RUTA: Página de espera de aprobación
    Route::get('register/pending', [RegisterController::class, 'pending'])->name('register.pending');
    
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    
    Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
    Route::get('password/reset/{email}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/update', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth:driver'])->group(function () {
    Route::post('/telemetry/update', [TelemetryController::class, 'update'])->name('telemetry.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders/poll', [DashboardController::class, 'pollPendingOrders'])->name('orders.poll');
    Route::post('/orders/{id}/take', [DashboardController::class, 'takeOrder'])->name('orders.take');
    Route::post('/orders/{id}/arrived', [DashboardController::class, 'markAsArrived'])->name('orders.arrived');
    Route::post('/orders/{id}/complete', [DashboardController::class, 'completeOrder'])->name('orders.complete');
    Route::post('/orders/{id}/pickup', [DashboardController::class, 'verifyPickup'])->name('orders.verify-pickup');
    Route::post('/upload-docs', [DriverProfileController::class, 'uploadDocs'])->name('upload-docs');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [DriverProfileController::class, 'index'])->name('index'); 
        Route::patch('/', [DriverProfileController::class, 'update'])->name('update'); 
    });
    
    Route::post('/status/toggle', [DashboardController::class, 'toggleStatus'])->name('status.toggle');
});