<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- CONTROLADORES DE AUTENTICACIÓN Y PERFIL ---
use App\Http\Controllers\PublicController;
use App\Modules\Identity\Controllers\AuthController;
use App\Modules\Identity\Controllers\ProfileWizardController;
use App\Modules\Identity\Controllers\PasswordResetController;
use App\Modules\Identity\Controllers\ProfileController;
use App\Modules\Identity\Controllers\VerificationController;
use App\Modules\Identity\Controllers\DashboardController;

// --- CONTROLADORES ADMIN (BACK-OFFICE) ---
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SkuController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RemovalController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\LogisticsDashboardController;
use App\Http\Controllers\Admin\TransformationController;

// --- CONTROLADORES TIENDA (FRONT-OFFICE) ---
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\LocationController;

// =============================================================================
// 1. ZONA PÚBLICA (LANDING PAGE & CATÁLOGO)
// =============================================================================

// La raíz ahora es la Tienda. Si es Invitado ve sin precios, si es Cliente ve con precios.
Route::get('/', [CatalogController::class, 'index'])->name('shop.index');

// Detalle de Producto (SEO friendly)
Route::get('/p/{id}', [CatalogController::class, 'show'])->name('shop.show');

Route::get('/', [CatalogController::class, 'index'])->name('shop.index');
Route::get('/p/{id}', [CatalogController::class, 'show'])->name('shop.show');

// NUEVAS RUTAS LEGALES
Route::get('/terms', function () { 
    return Inertia::render('Legal/Terms'); 
})->name('terms.show');

Route::get('/privacy', function () { 
    return Inertia::render('Legal/Privacy'); 
})->name('privacy.show');


// =============================================================================
// 2. AUTENTICACIÓN (GUEST)
// =============================================================================
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    
    // Recuperación de Contraseña
    Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');


// =============================================================================
// 3. ZONA CLIENTE & PERFIL (AUTENTICADOS)
// =============================================================================
// Aquí entran todos: Clientes, Staff, Admins. Es la zona común.
Route::middleware(['auth'])->group(function () {
    
    // A. Gestión de Identidad (Onboarding & Verify)
    Route::get('/onboarding', [ProfileWizardController::class, 'show'])->name('profile.wizard');
    Route::post('/onboarding/step-1', [ProfileWizardController::class, 'storeStep1'])->name('profile.step1');
    Route::post('/onboarding/step-2', [ProfileWizardController::class, 'storeStep2'])->name('profile.step2');
    
    Route::get('/email/verify', function () { return Inertia::render('Auth/VerifyEmail'); })->name('verification.notice');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/verify', [VerificationController::class, 'store'])->name('profile.verify'); 
    
    Route::get('/storage/avatars/{filename}', function ($filename) {
        $path = storage_path("app/private/avatars/" . auth()->id() . "/" . $filename);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    })->name('avatar.download');

    // B. E-COMMERCE (Funciones de Compra - Requiere Auth para ver precios/comprar)
    // Selección de Sucursal (Para el mapa o selector en header)
    Route::post('/shop/set-location', [LocationController::class, 'setLocation'])->name('shop.setLocation');
    
    // Carrito de Compras
    Route::get('/cart', [OrderController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [OrderController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{id}', [OrderController::class, 'remove'])->name('cart.remove');
    
    // Checkout y Pedidos
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store'); // Crea la orden (Pending Payment)
    Route::post('/checkout/{id}/upload-proof', [OrderController::class, 'uploadProof'])->name('checkout.upload'); // Sube el QR
    
    Route::get('/my-orders', [OrderController::class, 'history'])->name('orders.history');
    Route::get('/my-orders/{id}', [OrderController::class, 'show'])->name('orders.show');
});


// =============================================================================
// 4. ZONA ADMINISTRATIVA (ERP / BACK-OFFICE)
// =============================================================================
// Protegido por permiso 'view_admin_dashboard'. Clientes NO entran aquí.
Route::middleware(['auth', 'verified', 'can:view_admin_dashboard'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // --- DASHBOARD INTELIGENTE ---
        // El controlador decide qué vista mostrar (Gerencial, Operativa, Auditoría)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Dashboard específico de Logística (Mapas, rutas)
        Route::get('/logistics-dashboard', [LogisticsDashboardController::class, 'index'])->name('logistics.dashboard');


        // --- GRUPO 1: CONFIGURACIÓN CRÍTICA (Solo Super Admin) ---
        Route::middleware(['role:super_admin'])->group(function () {
            Route::resource('branches', BranchController::class);
            // ELIMINAR DE AQUÍ: Route::resource('users', UserController::class);
            
            Route::post('removals/{id}/approve', [RemovalController::class, 'approve'])->name('removals.approve');
            Route::post('removals/{id}/reject', [RemovalController::class, 'reject'])->name('removals.reject');
        });

        // --- GESTIÓN DE USUARIOS (Compartido: Super Admin + Branch Admin) ---
        // La seguridad la maneja el UserController y UserPolicy
        // --- GESTIÓN GENERAL (Compartido con restricciones por Policy) ---
        Route::resource('users', UserController::class);
        Route::resource('providers', ProviderController::class);


        // --- GRUPO 2: CATÁLOGO DE PRODUCTOS (Logistics + Super Admin) ---
        // Protegido por permiso 'manage_catalog'. El Branch Admin NO entra aquí.
        Route::middleware(['permission:manage_catalog'])->group(function () {
            // ELIMINAR DE AQUÍ: Route::resource('providers', ProviderController::class);
            
            Route::resource('brands', BrandController::class);
            Route::resource('categories', CategoryController::class);
            Route::resource('products', ProductController::class);
            Route::resource('skus', SkuController::class)->only(['store', 'destroy']);
        });


        // --- GRUPO 3: OPERACIONES DIARIAS (Inventory + Logistics + Super Admin) ---
        // Protegido por permiso 'view_inventory'. Aquí ocurre el movimiento de cajas.
        Route::middleware(['permission:view_inventory'])->group(function () {
            
            // A. Compras (Ingresos)
            Route::resource('purchases', PurchaseController::class);

            // B. Inventario (Kardex y Stock)
            Route::get('inventory/search', [InventoryController::class, 'search'])->name('inventory.search');
            Route::resource('inventory', InventoryController::class)->only(['index']); // Solo listar (Create se hace vía Purchase)

            // C. Bajas y Mermas (Solicitud)
            Route::get('removals', [RemovalController::class, 'index'])->name('removals.index');
            Route::get('removals/create', [RemovalController::class, 'create'])->name('removals.create');
            Route::post('removals', [RemovalController::class, 'store'])->name('removals.store');

            // D. Transferencias (Movimiento entre sucursales)
            Route::resource('transfers', TransferController::class)->only(['index', 'create', 'store', 'show']);
            Route::post('transfers/{id}/receive', [TransferController::class, 'receive'])->name('transfers.receive');

            // E. Transformaciones (Desglose: Caja -> Unidades)
            Route::get('transformations', [TransformationController::class, 'index'])->name('transformations.index');
            Route::get('transformations/create', [TransformationController::class, 'create'])->name('transformations.create');
            Route::post('transformations', [TransformationController::class, 'store'])->name('transformations.store');
        });
    });

// 5. REDIRECCIÓN DEFAULT (Legacy)
// Si alguien intenta entrar a /dashboard directamente y no es admin, lo mandamos a la tienda o perfil.
// (Esta ruta suele ser usada por Jetstream/Breeze por defecto)
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    // Si tiene permiso de admin, va al admin. Si no, a la tienda.
    if (auth()->user()->can('view_admin_dashboard')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('shop.index');
})->name('dashboard');