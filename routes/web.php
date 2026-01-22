<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- CONTROLADORES ---
use App\Http\Controllers\PublicController;
use App\Modules\Identity\Controllers\AuthController;
use App\Modules\Identity\Controllers\PasswordResetController;
use App\Modules\Identity\Controllers\ProfileController;
use App\Modules\Identity\Controllers\VerificationController;
use App\Modules\Identity\Controllers\DashboardController;

// BACK-OFFICE (ADMIN)
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SkuController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RemovalController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\LogisticsDashboardController;
use App\Http\Controllers\Admin\TransformationController;

// FRONT-OFFICE (TIENDA)
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\LocationController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;

use App\Http\Controllers\Admin\BundleController;

use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Driver\DriverController;


// =============================================================================
// 1. ZONA PÚBLICA (LANDING PAGE & CATÁLOGO)
// =============================================================================

Route::get('/', [CatalogController::class, 'index'])->name('shop.index');
Route::get('/p/{id}', [CatalogController::class, 'show'])->name('shop.show');

// Rutas Legales
Route::get('/terms', function () { return Inertia::render('Legal/Terms'); })->name('terms.show');
Route::get('/privacy', function () { return Inertia::render('Legal/Privacy'); })->name('privacy.show');


// =============================================================================
// 2. AUTENTICACIÓN (GUEST)
// =============================================================================
Route::middleware('guest')->group(function () {

    
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    
    // Validación asíncrona del Paso 1 (Nuevo Registro)
    Route::post('register/validate-step-1', [AuthController::class, 'validateStep1'])->name('register.validate-step-1');
    
    // Ruta placeholder para registro de conductores
    Route::get('register/driver', function () { return Inertia::render('Auth/RegisterDriver'); })->name('register.driver');

    // Recuperación de Contraseña
    // Paso 1: Solicitar
    Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');

    // Paso 2: Resetear (Notar que ya no usamos token en la URL, es un formulario POST limpio)
    Route::get('reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset'); // Cambié la ruta para no pedir token en URL
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});
 
Route::post('logout',[AuthController::class, 'logout'])->name('logout');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index'); // Ver carrito
    Route::post('/', [CartController::class, 'store'])->name('store'); // Agregar item
    Route::put('/{id}', [CartController::class, 'update'])->name('update'); // Cambiar cantidad
    Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy'); // Quitar item
});
Route::get('/bundles', [App\Http\Controllers\Shop\BundleController::class, 'index'])->name('shop.bundles.index');
Route::post('/bundles/{bundle}/add', [App\Http\Controllers\Shop\BundleController::class, 'addToCart'])->name('shop.bundles.add');

// =============================================================================
// 3. ZONA CLIENTE & PERFIL (AUTENTICADOS)
// =============================================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Rutas de Órdenes (Historial y Detalles)
    Route::get('/orders/{code}', [OrderController::class, 'show'])->name('orders.show');
    // A. CENTRO DE MANDO (PERFIL)
    // -------------------------------------------------------------
    // 1. Dashboard del Perfil (Solo Lectura - Resumen)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    
    // 2. Edición de Datos Personales (Formulario)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // 3. Verificación de Identidad (Subida de Docs)
    Route::get('/profile/verification', function() { return Inertia::render('Profile/Verification'); })->name('profile.verification');
    Route::post('/profile/verify', [VerificationController::class, 'store'])->name('profile.verify'); 
    
    // 4. Sub-módulos (Direcciones y Seguridad)
    Route::get('/profile/security', function() { return Inertia::render('Profile/Security'); })->name('profile.security');

    // Utilitarios
    Route::get('/email/verify', function () { return Inertia::render('Auth/VerifyEmail'); })->name('verification.notice');
    Route::get('/storage/avatars/{filename}', function ($filename) {
        $path = storage_path("app/private/avatars/" . auth()->id() . "/" . $filename);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    })->name('avatar.download');
    Route::middleware(['auth'])->group(function () {
        Route::resource('addresses', AddressController::class);
    });
    // ... rutas anteriores ...

    // Gestión de Avatar
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // Verificación de Email
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', '¡Enlace de verificación enviado!');
    })->middleware(['throttle:6,1'])->name('verification.send'); // Máximo 6 intentos por minuto

    // Validar contraseña actual (para cambios sensibles)
    Route::post('/user/confirm-password', [AuthController::class, 'confirmPassword'])->name('user.confirm-password');
    // B. E-COMMERCE (Funciones de Compra)
    // -------------------------------------------------------------
    
    Route::post('/shop/set-location', [LocationController::class, 'setLocation'])->name('shop.setLocation');
    
    // --- AQUÍ ESTABA EL ERROR ---
    // Borra las líneas viejas que usan OrderController para el checkout
    
    // Rutas Correctas usando CheckoutController:
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index'); // <--- FALTABA ESTA (Error Ziggy)
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store'); // <--- CORREGIDO (Usar CheckoutController)

    // Rutas de Seguimiento y Pago (OrderController está bien aquí)
    Route::post('/checkout/{id}/upload-proof', [OrderController::class, 'uploadProof'])->name('checkout.upload');
    
    Route::get('/my-orders', [OrderController::class, 'history'])->name('orders.history');
    Route::get('/my-orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // ... dentro de Route::middleware(['auth']) ...

    // ZONA CONDUCTORES
    Route::middleware(['role:driver'])->prefix('driver')->name('driver.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Driver\DriverController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [App\Http\Controllers\Driver\DriverController::class, 'history'])->name('history');
        Route::post('/upload-docs', [App\Http\Controllers\Driver\DriverController::class, 'uploadDocuments'])->name('upload-docs');
    });
    // AGREGAR ESTAS 2 LÍNEAS PARA QUE ZIGGY RECONOZCA LAS RUTAS
    
});


// =============================================================================
// 4. ZONA ADMINISTRATIVA (ERP / BACK-OFFICE)
// =============================================================================
Route::middleware(['auth', 'verified', 'can:view_admin_dashboard'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/logistics-dashboard', [LogisticsDashboardController::class, 'index'])->name('logistics.dashboard');

        // GESTIÓN CRÍTICA (Solo Super Admin)
        Route::middleware(['role:super_admin'])->group(function () {
            Route::resource('branches', BranchController::class);
            Route::resource('bundles', BundleController::class);
            Route::post('removals/{id}/approve', [RemovalController::class, 'approve'])->name('removals.approve');
            Route::post('removals/{id}/reject', [RemovalController::class, 'reject'])->name('removals.reject');
                        // GESTIÓN DE PRECIOS
            Route::get('prices', [App\Http\Controllers\Admin\PriceController::class, 'index'])->name('prices.index');
            Route::post('prices', [App\Http\Controllers\Admin\PriceController::class, 'store'])->name('prices.store');
        });

        // GESTIÓN GENERAL
        Route::resource('users', UserController::class);
        Route::resource('providers', ProviderController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('products', ProductController::class);
       // Corrección: Agregamos 'update' a la lista de métodos permitidos
        Route::resource('skus', SkuController::class)->only(['store', 'update', 'destroy']);
        Route::resource('purchases', PurchaseController::class);
        Route::resource('transfers', TransferController::class);

        // OPERACIONES DIARIAS
        Route::get('/inventory/stock/{branch}', [InventoryController::class, 'getStockByBranch'])->name('inventory.stock-by-branch');
        
        Route::middleware(['permission:view_inventory'])->group(function () {
            Route::get('inventory/search', [InventoryController::class, 'search'])->name('inventory.search');
            Route::resource('inventory', InventoryController::class)->only(['index']);
            
            Route::get('removals', [RemovalController::class, 'index'])->name('removals.index');
            Route::get('removals/create', [RemovalController::class, 'create'])->name('removals.create');
            Route::post('removals', [RemovalController::class, 'store'])->name('removals.store');

            Route::post('transfers/{id}/receive', [TransferController::class, 'receive'])->name('transfers.receive');

            Route::get('transformations', [TransformationController::class, 'index'])->name('transformations.index');
            Route::get('transformations/create', [TransformationController::class, 'create'])->name('transformations.create');
            Route::post('transformations', [TransformationController::class, 'store'])->name('transformations.store');
        });
        Route::get('orders/kanban', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.kanban');
        Route::patch('orders/{id}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        // En routes/web.php, dentro del grupo 'admin.'
        Route::resource('drivers', App\Http\Controllers\Admin\DriverController::class);
    });

// 5. REDIRECCIÓN DEFAULT
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    // Si tiene permiso de admin, va al admin. Si no, al Centro de Mando del Perfil.
    if (auth()->user()->can('view_admin_dashboard')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('profile.index');
})->name('dashboard');

