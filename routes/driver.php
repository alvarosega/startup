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

// routes/Driver.php

// ... (imports)

Route::middleware(['auth:driver'])->group(function () {
    // 1. Estado y Perfil
    Route::post('/status/toggle', [DashboardController::class, 'toggleStatus'])->name('status.toggle');
    Route::get('/profile', [DriverProfileController::class, 'index'])->name('profile.index');
    Route::post('/upload-docs', [DriverProfileController::class, 'uploadDocs'])->name('upload-docs');
    
    // 2. Módulo de Órdenes
    Route::get('/orders', [App\Http\Controllers\Web\Driver\Order\OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{id}/take', [App\Http\Controllers\Web\Driver\Order\OrderController::class, 'take'])->name('orders.take');
    
    // 3. Módulo de Ruta (Dashboard)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/orders/{id}/arrived', [App\Http\Controllers\Web\Driver\Order\OrderController::class, 'markAsArrived'])->name('orders.arrived');
    Route::post('/orders/{id}/complete', [App\Http\Controllers\Web\Driver\Order\OrderController::class, 'completeOrder'])->name('orders.complete');
    
    // 4. Historial y Salida
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    
    // RECTIFICACIÓN: Solo 'logout'. El prefijo 'driver.' se heredará automáticamente.
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout'); 
});