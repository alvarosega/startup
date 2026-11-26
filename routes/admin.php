<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Admin\Auth\LoginController;
use App\Http\Controllers\Web\Admin\Bundle\BundleController;
use App\Http\Controllers\Web\Admin\Catalog\BrandController;
use App\Http\Controllers\Web\Admin\Catalog\CategoryController;
use App\Http\Controllers\Web\Admin\Catalog\ProductController;
use App\Http\Controllers\Web\Admin\Catalog\SkuController;
use App\Http\Controllers\Web\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Web\Admin\Inventory\InventoryController;
use App\Http\Controllers\Web\Admin\Inventory\PriceController;
use App\Http\Controllers\Web\Admin\Inventory\PurchaseController;
use App\Http\Controllers\Web\Admin\Logistics\MonitorController;
use App\Http\Controllers\Web\Admin\Operations\BranchController;
use App\Http\Controllers\Web\Admin\Operations\ProviderController;
use App\Http\Controllers\Web\Admin\Operations\BranchDailyCashController;
use App\Http\Controllers\Web\Admin\Operations\BranchScheduleController;
use App\Http\Controllers\Web\Admin\Operations\BranchScheduleExceptionController;
use App\Http\Controllers\Web\Admin\Order\OrderController;
use App\Http\Controllers\Web\Admin\Inventory\RemovalController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCampaignController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCreativeController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdPlacementController;
use App\Http\Controllers\Web\Admin\User\CustomerController;
use App\Http\Controllers\Web\Admin\User\DriverController;
use App\Http\Controllers\Web\Admin\User\StaffController;
use Illuminate\Support\Facades\Route;

// Redirección de la raíz del subdominio admin
Route::get('/', function () {
    return \Illuminate\Support\Facades\Auth::guard('super_admin')->check()
        ? redirect()->route('dashboard.index')
        : redirect()->route('admin.login');
});

// Rutas de invitados (No autenticados)
Route::middleware(['guest:super_admin'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login.store');
});

// Rutas autenticadas bajo el guard super_admin
Route::middleware(['auth:super_admin'])->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Gestión de Usuarios (Clientes, Repartidores y Personal)
    Route::middleware('role_or_permission:super_admin|manage_users,super_admin')->group(function () {
        Route::group(['prefix' => 'users/customers', 'as' => 'customers.'], function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::patch('/{id}/status', [CustomerController::class, 'changeStatus'])->name('change-status');
            Route::post('/search-deleted', [CustomerController::class, 'searchDeleted'])->name('search-deleted');
            Route::post('/{id}/restore', [CustomerController::class, 'restoreDeleted'])->name('restore');
        });

        Route::group(['prefix' => 'users/staff', 'as' => 'staff.'], function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::get('/create', [StaffController::class, 'create'])->name('create');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::get('/{admin}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('/{admin}', [StaffController::class, 'update'])->name('update');
            Route::patch('/{admin}/toggle-status', [StaffController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    Route::middleware('role_or_permission:super_admin|manage_drivers|manage_users,super_admin')->group(function () {
        Route::group(['prefix' => 'users/drivers', 'as' => 'drivers.'], function () {
            Route::get('/', [DriverController::class, 'index'])->name('index');
            Route::get('/create', [DriverController::class, 'create'])->name('create');
            Route::post('/', [DriverController::class, 'store'])->name('store');
            Route::patch('/{id}/status', [DriverController::class, 'changeStatus'])->name('change-status');
            Route::get('/{id}/document/{type}', [DriverController::class, 'viewDocument'])->name('document');
            Route::post('/search-deleted', [DriverController::class, 'searchDeleted'])->name('search-deleted');
            Route::post('/{id}/restore', [DriverController::class, 'restoreDeleted'])->name('restore');
        });
    });

    Route::name('admin.')->group(function () {

        // Catálogo y Combos
        Route::middleware('role_or_permission:super_admin|manage_catalog,super_admin')->group(function () {
            Route::prefix('catalog')->name('catalog.')->group(function () {
                Route::prefix('products')->name('products.')->group(function () {
                    Route::get('reorder', [ProductController::class, 'reorder'])->name('reorder');
                    Route::patch('reorder', [ProductController::class, 'updateOrder'])->name('reorder.update');
                });
                Route::get('products/check-name', [ProductController::class, 'checkName'])->name('products.check-name');
                Route::resource('products', ProductController::class);

                Route::get('products/{product}/skus/create', [SkuController::class, 'create'])->name('products.skus.create');
                Route::post('products/{product}/skus', [SkuController::class, 'store'])->name('products.skus.store');
                Route::get('skus/{sku}/edit', [SkuController::class, 'edit'])->name('skus.edit');
                Route::resource('skus', SkuController::class)->only(['store', 'update', 'destroy']);
                
                $categoryRoutes = function () {
                    Route::get('{category}/skus', [CategoryController::class, 'skus'])->name('skus');
                    Route::put('{category}/sku-order', [CategoryController::class, 'updateSkuOrder'])->name('update-sku-order');
                };
                Route::prefix('categories')->name('categories.')->group($categoryRoutes);
                Route::resource('categories', CategoryController::class)->except(['show']);
                Route::resource('brands', BrandController::class);
            });

            Route::get('bundles/{id}/items', [BundleController::class, 'items'])->name('bundles.items');
            Route::resource('bundles', BundleController::class)->except(['show']);
        });

        // Operaciones y Sucursales
        Route::prefix('operations')->name('operations.')->group(function () {

            // Control de Sucursales
            Route::middleware('role_or_permission:super_admin|manage_branches,super_admin')->group(function () {
                Route::patch('branches/{branch}/toggle-pause', [BranchController::class, 'togglePause'])->name('branches.toggle-pause');
                Route::resource('branches', BranchController::class)->except(['show']);
            });

            // Horarios Semanales y Excepciones
            Route::middleware('role_or_permission:super_admin|manage_branch_schedules,super_admin')->group(function () {
                Route::get('branches/{branch}/schedules', [BranchScheduleController::class, 'index'])->name('branches.schedules.index');
                Route::put('branches/{branch}/schedules', [BranchScheduleController::class, 'update'])->name('branches.schedules.update');
                Route::get('branches/{branch}/schedule-exceptions', [BranchScheduleExceptionController::class, 'index'])->name('branches.schedule-exceptions.index');
                Route::post('branches/{branch}/schedule-exceptions', [BranchScheduleExceptionController::class, 'store'])->name('branches.schedule-exceptions.store');
                Route::delete('branches/{branch}/schedule-exceptions/{exception}', [BranchScheduleExceptionController::class, 'destroy'])->name('branches.schedule-exceptions.destroy');
            });

            // Control y Arqueo de Caja Diaria
            Route::middleware('role_or_permission:super_admin|manage_branch_cash,super_admin')->group(function () {
                Route::get('branches/{branch}/cash', [BranchDailyCashController::class, 'index'])->name('branches.cash.index');
                Route::post('branches/{branch}/cash/open', [BranchDailyCashController::class, 'open'])->name('branches.cash.open');
                Route::post('branches/{branch}/cash/close', [BranchDailyCashController::class, 'close'])->name('branches.cash.close');
            });

            // Proveedores
            Route::middleware('role_or_permission:super_admin|manage_catalog,super_admin')->group(function () {
                Route::resource('providers', ProviderController::class);
            });
        });

        // Inventario / Stock
        Route::middleware('role_or_permission:super_admin|manage_inventory,super_admin')->group(function () {
            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [InventoryController::class, 'index'])->name('index');
                Route::get('/search', [InventoryController::class, 'search'])->name('search');
                Route::get('/{skuId}/kardex', [InventoryController::class, 'kardex'])->name('kardex');
                Route::get('/{skuId}/lots', [InventoryController::class, 'lots'])->name('lots');
                Route::post('/transfer-safety', [InventoryController::class, 'transferToSafety'])->name('transfer-safety');
                Route::post('/isolate-quarantine', [InventoryController::class, 'isolateToQuarantine'])->name('isolate-quarantine');
            });

            Route::get('transfers', fn() => 'Lista de transferencias en desarrollo')->name('transfers.index');
            Route::get('transfers/{id}/receive', fn() => 'Formulario de recepción en desarrollo')->name('transfers.receive');
            Route::post('transfers/{id}/reception', fn() => 'Procesamiento de recepción en desarrollo')->name('transfers.reception');
        });

        // Compras / Ingresos
        Route::middleware('role_or_permission:super_admin|manage_purchases,super_admin')->group(function () {
            Route::prefix('purchases')->name('purchases.')->group(function () {
                Route::get('/', [PurchaseController::class, 'index'])->name('index');
                Route::get('/create', [PurchaseController::class, 'create'])->name('create');
                Route::post('/', [PurchaseController::class, 'store'])->name('store');
                Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
                Route::put('/{purchase}', [PurchaseController::class, 'complete'])->name('complete');
                Route::post('/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
            });
        });

        // Precios
        Route::middleware('role_or_permission:super_admin|manage_prices,super_admin')->group(function () {
            Route::prefix('prices')->name('prices.')->group(function () {
                Route::get('/', [PriceController::class, 'index'])->name('index');
                Route::get('/create', [PriceController::class, 'create'])->name('create');
                Route::post('/', [PriceController::class, 'store'])->name('store');
                Route::delete('/{price}', [PriceController::class, 'destroy'])->name('destroy');
            });
        });

        // Bajas / Removals
        Route::middleware('role_or_permission:super_admin|manage_removals,super_admin')->group(function () {
            Route::prefix('removals')->name('removals.')->group(function () {
                Route::get('/', [RemovalController::class, 'index'])->name('index');
                Route::get('/create', [RemovalController::class, 'create'])->name('create');
                Route::post('/', [RemovalController::class, 'store'])->name('store');
            });
        });

        // Radar Logístico
        Route::middleware('role_or_permission:super_admin|view_logistics_radar,super_admin')->group(function () {
            Route::get('/logistics/monitor', [MonitorController::class, 'index'])->name('logistics.monitor');
        });
        
        // Órdenes
        Route::middleware('role_or_permission:super_admin|manage_orders,super_admin')->group(function () {
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('index'); 
                Route::get('/{order:code}', [OrderController::class, 'show'])->name('show'); 
                Route::get('/{order:code}/proof', [OrderController::class, 'showProof'])->name('show-proof');
                Route::post('/{order:code}/approve-payment', [OrderController::class, 'approvePayment'])->name('approve-payment');
                Route::post('/{order:code}/reject-payment', [OrderController::class, 'rejectPayment'])->name('reject-payment');
                Route::post('/{order:code}/ready', [OrderController::class, 'markAsReady'])->name('mark-as-ready');
                Route::post('/{order:code}/dispatch', [OrderController::class, 'dispatchOrder'])->name('dispatch');
            });
        });
    });
});