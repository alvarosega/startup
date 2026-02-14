<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// --- CONTROLADORES CUSTOMER ---
use App\Http\Controllers\Web\Customer\Auth\LoginController as CustomerLoginController;
use App\Http\Controllers\Web\Customer\Shop\ShopController;
use App\Http\Controllers\Web\Customer\Shop\CartController;
use App\Http\Controllers\Web\Customer\ProfileController;
use App\Http\Controllers\Web\Customer\AddressController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController;
use App\Http\Controllers\Web\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Web\Customer\Auth\RegisterController as CustomerRegisterController;
use App\Http\Controllers\Web\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Customer\Auth\ResetPasswordController;
//use App\Http\Controllers\Web\Customer\CheckoutController; // Asegúrate de tener este import si usas Checkout

// --- CONTROLADORES ADMIN ---
use App\Http\Controllers\Web\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Admin\BranchController;
use App\Http\Controllers\Web\Admin\ProductController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\MarketZoneController;
use App\Http\Controllers\Web\Admin\BundleController;
use App\Http\Controllers\Web\Admin\BrandController;
use App\Http\Controllers\Web\Admin\ProviderController;
use App\Http\Controllers\Web\Admin\SkuController;
use App\Http\Controllers\Web\Admin\PriceController;
use App\Http\Controllers\Web\Admin\InventoryController;
use App\Http\Controllers\Web\Admin\PurchaseController;
use App\Http\Controllers\Web\Admin\TransferController;
use App\Http\Controllers\Web\Admin\RemovalController;
use App\Http\Controllers\Web\Admin\TransformationController;
use App\Http\Controllers\Web\Admin\OrderController as AdminOrderController; // Alias para evitar colisión
use App\Http\Controllers\Web\Admin\DriverController;



use App\Http\Controllers\Web\Driver\Auth\LoginController as DriverLoginController;
use App\Http\Controllers\Web\Driver\Auth\RegisterController as DriverRegisterController;
use App\Http\Controllers\Web\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Web\Driver\ProfileController as DriverProfileController;
use App\Http\Middleware\HandleDriverInertiaRequests;
use App\Http\Controllers\Web\Driver\Auth\ForgotPasswordController as DriverForgotController;
use App\Http\Controllers\Web\Driver\Auth\ResetPasswordController as DriverResetController;

$adminPath = env('ADMIN_PATH', 'admin');

// =============================================================================
// GRUPO 1: CLIENTES & TIENDA (Middleware: inertia.customer)
// =============================================================================
// Este middleware carga el carrito, branches y datos específicos del cliente.
// Evita cargar datos pesados para rutas que no son de cliente.

Route::middleware(['inertia.customer'])->group(function () {

    // --- LANDING PAGE & CATÁLOGO ---
    Route::get('/', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/zone/{zone:slug}', [ShopController::class, 'showZone'])->name('shop.zone');

    // --- AUTENTICACIÓN CLIENTE (GUEST) ---
    Route::middleware('guest:customer')->group(function () {
        // Login
        Route::get('login', [CustomerLoginController::class, 'show'])->name('login');
        Route::post('login', [CustomerLoginController::class, 'store']); // Ya no se llama 'login.store' sino 'login' post
    
        // Registro
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
    Route::middleware(['auth:customer'])->group(function () {
        
        // Perfil
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

        // Direcciones
        Route::resource('addresses', AddressController::class);
        Route::patch('/addresses/{address}/default', [App\Http\Controllers\Web\Customer\AddressController::class, 'makeDefault'])
            ->name('addresses.set-default');

        // Checkout & Pedidos
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::get('/my-orders', function() { return Inertia::render('Customer/Orders/Index'); })->name('orders.history');
    });

    // --- CARRITO DE COMPRAS (Míxto: Guest + Auth) ---
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index'); 
        Route::post('/add', [CartController::class, 'store'])->name('add');
        Route::post('/bulk', [CartController::class, 'bulkStore'])->name('bulk');
        Route::patch('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('remove');
    });

    // --- DEBUGGING (Solo Local) ---
    if (app()->environment('local')) {
        Route::get('/debug-auth', function () {
             $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();
             return response()->json([
                 'status' => $customer ? 'LOGUEADO' : 'GUEST',
                 'id' => $customer?->id ? bin2hex($customer->getRawOriginal('id')) : null
             ]);
        });
    }
});


// =============================================================================
// GRUPO 2: ADMIN / BACKOFFICE (Middleware: inertia.admin)
// =============================================================================
// Este middleware es ligero. NO carga carrito ni branches públicas.

Route::prefix($adminPath)->name('admin.')->middleware(['inertia.admin'])->group(function () {

    // --- LOGIN ADMIN (GUEST) ---
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminLoginController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminLoginController::class, 'login'])->name('login.store');
    });

    // --- PANEL ADMIN (AUTH) ---
    Route::middleware(['auth:admin'])->group(function () {
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // --- RECURSOS ---
        Route::resource('users', UserController::class);
        Route::resource('drivers', DriverController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('market-zones', MarketZoneController::class);
        Route::resource('bundles', BundleController::class);
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
        Route::post('transfers/{id}/receive', [TransferController::class, 'receive'])->name('transfers.receive');

        Route::resource('removals', RemovalController::class);
        Route::post('removals/{id}/approve', [RemovalController::class, 'approve'])->name('removals.approve');
        Route::post('removals/{id}/reject', [RemovalController::class, 'reject'])->name('removals.reject');
        
        Route::resource('transformations', TransformationController::class);

        // Pedidos (Admin View)
        Route::get('orders/kanban', [AdminOrderController::class, 'index'])->name('orders.kanban');
        Route::patch('orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    });
});

// routes/web.php

// MODIFICACIÓN: Añadir middleware 'inertia.driver' al grupo
Route::prefix('driver')->name('driver.')->middleware(['inertia.driver'])->group(function () {

    Route::middleware('guest:driver')->group(function () {
        // --- ESTA ES LA RUTA QUE FALTA ---
        Route::get('register', [DriverRegisterController::class, 'create'])
            ->name('register'); 

        Route::get('login', [DriverLoginController::class, 'show'])->name('login');
        Route::post('login', [DriverLoginController::class, 'store']);
        
        Route::post('register/validate-step-1', [DriverRegisterController::class, 'validateStep1'])
            ->name('register.validate-step-1');
        
        Route::post('register/store', [DriverRegisterController::class, 'store'])
            ->name('register.store');
        Route::get('password/forgot', [DriverForgotController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [DriverForgotController::class, 'sendResetCode'])->name('password.email');
        
        Route::get('password/reset/{email}', [DriverResetController::class, 'showResetForm'])->name('password.reset');
Route::post('password/update', [DriverResetController::class, 'reset'])->name('password.update');
        
    });

    // --- APP PRIVADA (AUTH:DRIVER) ---
    Route::middleware(['auth:driver'])->group(function () {
        Route::post('logout', [DriverLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
        Route::get('/history', [DriverDashboardController::class, 'history'])->name('history');
        Route::get('/profile', [DriverProfileController::class, 'index'])->name('profile.index');
        Route::patch('/profile', [DriverProfileController::class, 'update'])->name('profile.update');
    });
});