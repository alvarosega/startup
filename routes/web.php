<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// --- CONTROLADORES CUSTOMER ---
use App\Http\Controllers\Web\Customer\Auth\LoginController as CustomerLoginController;
use App\Http\Controllers\Web\Customer\Shop\ShopController;
use App\Http\Controllers\Web\Customer\Cart\CartController;
use App\Http\Controllers\Web\Customer\Profiles\ProfileController;
use App\Http\Controllers\Web\Customer\Profiles\AddressController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController;
use App\Http\Controllers\Web\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController as CustomerRegisterController;
use App\Http\Controllers\Web\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Customer\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Customer\Cart\BundleController as CustomerBundleController;
use App\Http\Controllers\Web\Customer\Order\CheckoutController;
// --- CONTROLADORES ADMIN ---
use App\Http\Controllers\Web\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Admin\BranchController;
use App\Http\Controllers\Web\Admin\ProductController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\MarketZoneController;
use App\Http\Controllers\Web\Admin\BundleController as AdminBundleController;

use App\Http\Controllers\Web\Admin\BrandController;
use App\Http\Controllers\Web\Admin\ProviderController;
use App\Http\Controllers\Web\Admin\SkuController;
use App\Http\Controllers\Web\Admin\PriceController;
use App\Http\Controllers\Web\Admin\InventoryController;
use App\Http\Controllers\Web\Admin\PurchaseController;
//use App\Http\Controllers\Web\Admin\TransferController;
//use App\Http\Controllers\Web\Admin\RemovalController;
//use App\Http\Controllers\Web\Admin\TransformationController;
use App\Http\Controllers\Web\Admin\Order\OrderController;
use App\Http\Controllers\Web\Admin\DriverController;


use App\Http\Controllers\Web\Driver\Auth\LoginController as DriverLoginController;
use App\Http\Controllers\Web\Driver\Auth\RegisterController as DriverRegisterController;
use App\Http\Controllers\Web\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Web\Driver\Profile\DriverProfileController;
use App\Http\Middleware\HandleDriverInertiaRequests;
use App\Http\Controllers\Web\Driver\Auth\ForgotPasswordController as DriverForgotController;
use App\Http\Controllers\Web\Driver\Auth\ResetPasswordController as DriverResetController;

use Illuminate\Support\Facades\Http;

$adminPath = env('ADMIN_PATH', 'admin');

// =============================================================================
// GRUPO 1: CLIENTES & TIENDA (Middleware: inertia.customer)
// =============================================================================
// Este middleware carga el carrito, branches y datos específicos del cliente.
// Evita cargar datos pesados para rutas que no son de cliente.

Route::middleware(['inertia.customer'])->group(function () {

    // --- ENVOLVEMOS TODO EN EL NAME 'customer.' PARA ALINEAR CON EL LAYOUT ---
    Route::name('customer.')->group(function () {
        
        // 1. Catálogo
        Route::get('/', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/zone/{zone:slug}', [ShopController::class, 'showZone'])->name('shop.zone');

        // 2. Carrito (MOVER AQUÍ Y ELIMINAR EL OTRO BLOQUE)

        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index'); 
            Route::post('/add', [CartController::class, 'store'])->name('add');
            Route::post('/sync', [CartController::class, 'sync'])->name('sync');
            
            // --- RUTAS FALTANTES AÑADIDAS ---
            Route::patch('/{id}', [CartController::class, 'update'])->name('update'); // Para updateQuantity
            Route::delete('/{id}', [CartController::class, 'remove'])->name('remove'); // Para removeItem
            
            Route::get('/bundle/{bundle:slug}', [CustomerBundleController::class, 'show'])->name('bundle.show');
            Route::post('/bundle/add', [CustomerBundleController::class, 'add'])->name('bundle.add');
        });
    });

    // --- AUTENTICACIÓN CLIENTE (GUEST) ---
    // Mantener fuera del grupo de nombre 'customer.' si usas los nombres estándar de Laravel (login, register)
    Route::middleware('guest:customer')->group(function () {
        Route::get('login', [CustomerLoginController::class, 'show'])->name('login');
        Route::post('login', [CustomerLoginController::class, 'store']);
        Route::get('register', [CustomerRegisterController::class, 'create'])->name('register');
        Route::post('register', [CustomerRegisterController::class, 'store']);
        Route::post('register/validate-step-1', [CustomerRegisterController::class, 'validateStep1'])->name('register.validate-step-1');
        Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');

        // Paso 2: Ingresar código y nueva clave
        Route::get('password/reset/{email}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/update', [ResetPasswordController::class, 'reset'])->name('password.update');
    });

    // --- AUTENTICACIÓN CLIENTE (LOGOUT) ---
    Route::post('logout', [CustomerLoginController::class, 'destroy'])->name('logout');

    // --- ZONA PRIVADA CLIENTE (AUTH) ---
    Route::middleware(['auth:customer', 'inertia.customer'])->prefix('customer')->name('customer.')->group(function () {

        // --- MÓDULO: PERFIL (Páginas Independientes) ---
        Route::prefix('profile')->name('profile.')->group(function () {
            
            // A. VISTA: Información Personal (PersonalInfoPage.vue)
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
    
            // B. VISTA: Gestión de Direcciones (AddressesPage.vue)
            // La ruta GET renderiza la lista y el mapa
            Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    
            // C. VISTA: Seguridad (SecurityPage.vue)
            Route::get('/security', [ProfileController::class, 'security'])->name('security');
    
            // --- ACCIONES CRUD DE DIRECCIONES (Lógica de Negocio) ---
            Route::prefix('addresses')->name('addresses.')->group(function () {
                Route::post('/', [AddressController::class, 'store'])->name('store');
                Route::put('/{id}', [AddressController::class, 'update'])->name('update');
                Route::delete('/{id}', [AddressController::class, 'destroy'])->name('destroy');
                Route::patch('/{id}/default', [AddressController::class, 'makeDefault'])->name('set-default');
            });
        });
    
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Web\Customer\Order\OrderController::class, 'index'])->name('history');// El listado general (customer.orders.history)
            
            // Vista de detalle y pago
            Route::get('/{id}', [\App\Http\Controllers\Web\Customer\Order\OrderController::class, 'show'])
                ->name('show'); // customer.orders.show
                
            // Acción de subir comprobante
            Route::post('/{id}/proof', [\App\Http\Controllers\Web\Customer\Order\OrderController::class, 'uploadProof'])
                ->name('upload-proof'); // customer.orders.upload-proof
        });
    
    });



    // --- DEBUGGING (Solo Local) ---
    if (app()->environment('local')) {
        Route::get('/debug-auth', function () {
             $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();
             return response()->json([
                 'status' => $customer ? 'LOGUEADO' : 'GUEST',
                 'id' => $customer?->id
             ]);
        });
    }
});


// =============================================================================
// GRUPO 2: ADMIN / BACKOFFICE (Middleware: inertia.admin)
// =============================================================================
// Este middleware es ligero. NO carga carrito ni branches públicas.

Route::prefix($adminPath)->name('admin.')->group(function () {

    // --- LOGIN ADMIN (GUEST) ---
    Route::middleware(['inertia.admin', 'guest:super_admin'])->group(function () {
        Route::get('login', [AdminLoginController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminLoginController::class, 'login'])->name('login.store');
    });

    // --- PANEL ADMIN (AUTH) ---
    Route::middleware(['inertia.admin', 'auth:super_admin'])->group(function () {
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // --- RECURSOS ---
        Route::middleware('role:super_admin,super_admin')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('drivers', DriverController::class);
        
            Route::resource('branches', BranchController::class);
            Route::resource('products', ProductController::class);
            // Rutas Quirúrgicas para SKUs
            Route::get('products/check-name', [ProductController::class, 'checkName'])->name('products.check-name');
            Route::get('products/{product}/skus/create', [SkuController::class, 'create'])->name('products.skus.create');
            Route::post('products/{product}/skus', [SkuController::class, 'store'])->name('products.skus.store');
            Route::get('skus/{sku}/edit', [SkuController::class, 'edit'])->name('skus.edit');
            Route::resource('categories', CategoryController::class);
            Route::resource('market-zones', MarketZoneController::class)
                ->names('market-zones')
                ->parameters(['market-zones' => 'market_zone']);
            Route::resource('bundles', AdminBundleController::class);
            Route::resource('brands', BrandController::class);
            Route::resource('providers', ProviderController::class);
            Route::resource('skus', SkuController::class)->only(['store', 'update', 'destroy']);
            
            // Precios
            Route::get('prices', [PriceController::class, 'index'])->name('prices.index');
            Route::post('prices', [PriceController::class, 'store'])->name('prices.store');
    
            // Inventario
            Route::resource('inventory', InventoryController::class)->only(['index']);
            Route::get('/inventory/stock/{branch}', [InventoryController::class, 'getStockByBranch'])->name('inventory.stock-by-branch');
            Route::get('inventory/search', [InventoryController::class, 'search'])->name('inventory.search');
    
            // Operaciones
            Route::resource('purchases', PurchaseController::class);
            Route::resource('transfers', TransferController::class);
   //         Route::post('transfers/{id}/receive', [TransferController::class, 'receive'])->name('transfers.receive');
    
            Route::resource('removals', RemovalController::class);
       //     Route::post('removals/{id}/approve', [RemovalController::class, 'approve'])->name('removals.approve');
         //   Route::post('removals/{id}/reject', [RemovalController::class, 'reject'])->name('removals.reject');
            
            Route::resource('transformations', TransformationController::class);
    
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('index'); // Ahora sí es admin.orders.index
                
                // Motor Financiero
                Route::post('/{id}/approve-payment', [OrderController::class, 'approvePayment'])->name('approve-payment');
                Route::post('/{id}/reject-payment', [OrderController::class, 'rejectPayment'])->name('reject-payment');
                
                // Motor Logístico
                Route::post('/{id}/dispatch', [OrderController::class, 'dispatchOrder'])->name('dispatch');
            });
        });


    });
});

// =============================================================================
// GRUPO 3: DRIVERS (Middleware: inertia.driver)
// =============================================================================

Route::prefix('driver')->name('driver.')->middleware(['inertia.driver'])->group(function () {

    // --- ACCESO PÚBLICO (GUEST) ---
    Route::middleware('guest:driver')->group(function () {
        
        Route::get('register', [DriverRegisterController::class, 'create'])->name('register'); 
        Route::post('register/validate-step-1', [DriverRegisterController::class, 'validateStep1'])->name('register.validate-step-1');
        Route::post('register/store', [DriverRegisterController::class, 'store'])->name('register.store');
        
        Route::get('login', [DriverLoginController::class, 'show'])->name('login');
        Route::post('login', [DriverLoginController::class, 'store']); // El post no necesita nombre si hereda el url
        
        Route::get('password/forgot', [DriverForgotController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [DriverForgotController::class, 'sendResetCode'])->name('password.email');
        Route::get('password/reset/{email}', [DriverResetController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/update', [DriverResetController::class, 'reset'])->name('password.update');
    });

    // --- APP PRIVADA (AUTH:DRIVER) ---
    Route::middleware(['auth:driver'])->group(function () {
        
        // CUIDADO AQUÍ: El nombre base del grupo ya es 'driver.', 
        // por lo tanto ->name('dashboard') genera la ruta 'driver.dashboard'.
        // Nunca pongas ->name('driver.dashboard') aquí adentro.
        
        Route::get('/dashboard', [DriverProfileController::class, 'index'])->name('dashboard');
        
        Route::post('/upload-docs', [DriverProfileController::class, 'uploadDocs'])->name('upload-docs');
        Route::get('/history', [DriverDashboardController::class, 'history'])->name('history');
        Route::post('logout', [DriverLoginController::class, 'destroy'])->name('logout');
        
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [DriverProfileController::class, 'index'])->name('index'); // driver.profile.index
            Route::patch('/', [DriverProfileController::class, 'update'])->name('update'); // driver.profile.update
        });
    });
});

Route::get('/api/geo/reverse', function (Illuminate\Http\Request $request) {
    return Http::withHeaders(['User-Agent' => 'ElectricLuxury/1.0'])
        ->get("https://nominatim.openstreetmap.org/reverse", [
            'format' => 'json',
            'lat' => $request->lat,
            'lon' => $request->lng,
            'accept-language' => 'es'
        ])->json();
})->name('geo.reverse');