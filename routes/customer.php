<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Customer\Auth\LoginController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController;
use App\Http\Controllers\Web\Customer\Auth\LogoutController;
use App\Http\Controllers\Web\Customer\Shop\ShopController;

/*
|--------------------------------------------------------------------------
| Silo Customer: Sistema de Rutas Convalidadas
|--------------------------------------------------------------------------
| Prefix 'customer.' y Name 'customer.' aplicados en bootstrap/app.php
*/

// --- NAVEGACIÓN HÍBRIDA / PÚBLICA (PÁGINA PRINCIPAL) ---
// Resuelve el contexto geográfico para invitados (is_default) y autenticados (branch_id)
Route::get('/', ShopController::class)->name('index');

// --- PERÍMETRO EXCLUSIVO PARA INVITADOS (GUEST GUARD) ---
Route::middleware(['guest:customer'])->group(function (): void {
    
    // Autenticación de Cliente
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    // Registro de Cuenta & Orquestación de Cobertura Geográfica
    // El método GET atiende tanto la carga inicial como los Partial Reloads de Inertia (latitude/longitude)
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');
});

// --- PERÍMETRO PROTEGIDO (AUTH GUARD) ---
Route::middleware(['auth:customer'])->group(function (): void {
    
    // Cierre de Sesión Atómico (Recurso Singular Invocable)
    Route::delete('logout', LogoutController::class)->name('login.destroy');
});