<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;

Route::prefix('v1')->group(function () {
    // Estas rutas devuelven JSON Puro y usan Tokens
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        // Route::post('/register', [AuthController::class, 'register']); // Próximamente
        Route::post('/register', [AuthController::class, 'register']);
    });
    Route::middleware(['auth:sanctum', 'role:super_admin|branch_admin'])->prefix('admin')->group(function () {
        
        // CRUD de Usuarios vía API
        Route::apiResource('users', UserController::class);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
        
        Route::apiResource('categories', \App\Http\Controllers\Api\V1\Admin\CategoryController::class);
        Route::post('prices', [\App\Http\Controllers\Api\V1\Admin\PriceController::class, 'store']);
        Route::get('prices', [\App\Http\Controllers\Api\V1\Admin\PriceController::class, 'index']);
        Route::apiResource('branches', \App\Http\Controllers\Api\V1\Admin\BranchController::class);

    });
});