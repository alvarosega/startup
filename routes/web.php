<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- CONTROLADORES ---
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Web\Auth\WebAuthController; 
use App\Modules\Identity\Controllers\PasswordResetController;
use App\Http\Controllers\Client\ProfileController;
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
use App\Http\Controllers\Admin\MarketZoneController;



// FRONT-OFFICE (TIENDA)
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\LocationController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;

use App\Http\Controllers\Admin\BundleController;

use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Driver\DriverController;

use App\Http\Controllers\Shop\ShopController;



// =============================================================================
// 1. ZONA PÚBLICA (LANDING PAGE & CATÁLOGO)
// =============================================================================

Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/p/{id}', [CatalogController::class, 'show'])->name('shop.show');
Route::get('/shop/bundle/{slug}', [App\Http\Controllers\Shop\BundleController::class, 'show'])->name('shop.bundle.show');
// Rutas Legales
Route::get('/terms', function () { return Inertia::render('Legal/Terms'); })->name('terms.show');
Route::get('/privacy', function () { return Inertia::render('Legal/Privacy'); })->name('privacy.show');


// =============================================================================
// 2. AUTENTICACIÓN (GUEST)
// =============================================================================
Route::middleware('guest')->group(function () {

    
    Route::get('login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [WebAuthController::class, 'login'])->middleware('throttle:login');
    
    Route::get('register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [WebAuthController::class, 'register']);
    
    // Validación Asíncrona Paso 1
    Route::post('register/validate-step-1', [WebAuthController::class, 'validateStep1'])->name('register.validate-step-1');
        
    // Driver (Vista placeholder)
    Route::get('register/driver', function () { return Inertia::render('Auth/RegisterDriver'); })->name('register.driver');
    Route::post('register/driver', [WebAuthController::class, 'registerDriver'])->name('register.driver.store');
    // Recuperación de Contraseña
    // Paso 1: Solicitar
    Route::post('/forgot-password', [WebAuthController::class, 'sendRecoveryCode'])
    ->name('password.email');

    // 2. Validar código y cambiar password (POST desde el form de "Nueva Contraseña")
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])
        ->name('password.update');
});
 
Route::post('logout', [WebAuthController::class, 'logout'])->name('logout');



// Grupo de rutas para la tienda (con middleware de sesión/auth según necesites)
Route::prefix('shop')->name('cart.')->group(function () {
    
    // Ver Carrito
    Route::get('/cart', [CartController::class, 'index'])->name('index');
    
    // Agregar Item (POST)
    Route::post('/cart/add', [CartController::class, 'store'])->name('add');
    
    // Actualizar Cantidad (PATCH)
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('update');
    
    // Eliminar Item (DELETE)
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('remove');
    
    // Vaciar / Bulk (Opcional)
    Route::post('/cart/bulk', [CartController::class, 'bulkStore'])->name('bulk');
});

Route::get('/shop/zone/{zone}', [ShopController::class, 'showZone'])->name('shop.zone');
Route::get('/shop/zone/{zone:slug}', [ShopController::class, 'showZone'])
->name('shop.zone');
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
    
    // Avatar (POST separado para manejo de archivos limpio)
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
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
        Route::patch('/addresses/{address}/default', [AddressController::class, 'makeDefault'])->name('addresses.set-default');
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
    // CORRECCIÓN: Usar WebAuthController, NO AuthController
    Route::post('/user/confirm-password', [WebAuthController::class, 'confirmPassword'])
    ->name('user.confirm-password');

    // B. E-COMMERCE (Funciones de Compra)
    // -------------------------------------------------------------
    
    Route::post('/shop/set-location', [App\Http\Controllers\Shop\LocationController::class, 'setLocation'])->name('shop.setLocation');
    
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
    Route::middleware(['role:driver']) // Asegura que solo entren conductores
        ->prefix('driver')             // La URL será /driver/...
        ->name('driver.')              // Las rutas se llamarán driver....
        ->group(function () {
            
            // Dashboard y Historial
            Route::get('/dashboard', [App\Http\Controllers\Driver\DriverController::class, 'dashboard'])->name('dashboard');
            Route::get('/history', [App\Http\Controllers\Driver\DriverController::class, 'history'])->name('history');
            
            // Subida de Documentos
            Route::post('/upload-docs', [App\Http\Controllers\Driver\DriverController::class, 'uploadDocuments'])->name('upload-docs');
            
            // --- AQUÍ ESTÁN LAS RUTAS DEL PERFIL ---
            
            // 1. Mostrar el formulario (Esta es la que usa el botón del menú)
           // GESTIÓN DE PERFIL CONDUCTOR
            Route::get('/profile', [DriverController::class, 'indexProfile'])->name('profile.index'); 
            Route::get('/profile/edit', [DriverController::class, 'editProfile'])->name('profile.edit'); 
            Route::patch('/profile', [DriverController::class, 'updateProfile'])->name('profile.update');
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
            Route::resource('market-zones', MarketZoneController::class);
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
// 5. REDIRECCIÓN CENTRALIZADA (TRAFFIC CONTROLLER)
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = auth()->user();

    // 1. NIVEL LOGÍSTICO
    if ($user->hasRole('logistics_operator')) {
        return redirect()->route('admin.logistics.dashboard');
    }

    // 2. NIVEL CONDUCTOR (DriverLayout)
    if ($user->hasRole('driver')) {
        return redirect()->route('driver.dashboard');
    }

    // 3. NIVEL ADMINISTRATIVO (AdminLayout)
    // Usamos el permiso 'view_admin_dashboard' o un array de roles
    if ($user->can('view_admin_dashboard') || $user->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    }

    // 4. NIVEL CLIENTE (ShopLayout)
    // Si no es ninguno de los anteriores, es un cliente.
    // Aquí decidimos si va al Perfil o a la Tienda. Tú pediste ShopLayout:
    return redirect()->route('shop.index'); 
})->name('dashboard');

