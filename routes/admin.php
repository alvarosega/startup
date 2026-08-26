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
use App\Http\Controllers\Web\Admin\MarketZone\MarketZoneController;
use App\Http\Controllers\Web\Admin\Operations\BranchController;
use App\Http\Controllers\Web\Admin\Operations\ProviderController;
use App\Http\Controllers\Web\Admin\Operations\BranchDailyCashController;
use App\Http\Controllers\Web\Admin\Operations\BranchScheduleController;
use App\Http\Controllers\Web\Admin\Operations\BranchScheduleExceptionController;
use App\Http\Controllers\Web\Admin\Order\OrderController;
use App\Http\Controllers\Web\Admin\Removal\RemovalController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCampaignController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCreativeController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdPlacementController;
use App\Http\Controllers\Web\Admin\User\CustomerController;
use App\Http\Controllers\Web\Admin\User\DriverController;
use Illuminate\Support\Facades\Route;

// Rutas de invitados (No autenticados)
Route::middleware(['guest:super_admin'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login.store');
});

// Rutas autenticadas bajo el guard super_admin
Route::middleware(['auth:super_admin'])->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::middleware('role:super_admin,super_admin')->group(function () {
        
        Route::group(['prefix' => 'users/customers', 'as' => 'customers.'], function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::patch('/{id}/status', [CustomerController::class, 'changeStatus'])->name('change-status');
            Route::post('/search-deleted', [CustomerController::class, 'searchDeleted'])->name('search-deleted');
            Route::post('/{id}/restore', [CustomerController::class, 'restoreDeleted'])->name('restore');
        });

        Route::group(['prefix' => 'users/drivers', 'as' => 'drivers.'], function () {
            Route::get('/', [DriverController::class, 'index'])->name('index');
            Route::get('/create', [DriverController::class, 'create'])->name('create');
            Route::post('/', [DriverController::class, 'store'])->name('store');
            Route::patch('/{id}/status', [DriverController::class, 'changeStatus'])->name('change-status');
            Route::post('/search-deleted', [DriverController::class, 'searchDeleted'])->name('search-deleted');
            Route::post('/{id}/restore', [DriverController::class, 'restoreDeleted'])->name('restore');
        });
    });

    Route::middleware('role:super_admin,super_admin')->name('admin.')->group(function () {

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

        Route::prefix('operations')->name('operations.')->group(function () {

            // 1. Control Atómico de Kill Switch
            Route::patch('branches/{branch}/toggle-pause', [BranchController::class, 'togglePause'])->name('branches.toggle-pause');

            // 2. Horarios Semanales (0 a 6)
            Route::get('branches/{branch}/schedules', [BranchScheduleController::class, 'index'])->name('branches.schedules.index');
            Route::put('branches/{branch}/schedules', [BranchScheduleController::class, 'update'])->name('branches.schedules.update');

            // 3. Excepciones de Horario y Feriados
            Route::get('branches/{branch}/schedule-exceptions', [BranchScheduleExceptionController::class, 'index'])->name('branches.schedule-exceptions.index');
            Route::post('branches/{branch}/schedule-exceptions', [BranchScheduleExceptionController::class, 'store'])->name('branches.schedule-exceptions.store');
            Route::delete('branches/{branch}/schedule-exceptions/{exception}', [BranchScheduleExceptionController::class, 'destroy'])->name('branches.schedule-exceptions.destroy');

            // 4. Control y Arqueo de Caja Diaria
            Route::get('branches/{branch}/cash', [BranchDailyCashController::class, 'index'])->name('branches.cash.index');
            Route::post('branches/{branch}/cash/open', [BranchDailyCashController::class, 'open'])->name('branches.cash.open');
            Route::post('branches/{branch}/cash/close', [BranchDailyCashController::class, 'close'])->name('branches.cash.close');

            // 5. CRUD Principal de Sucursales y Proveedores
            Route::resource('branches', BranchController::class)->except(['show']);
            Route::resource('providers', ProviderController::class);
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/search', [InventoryController::class, 'search'])->name('search');
            Route::get('/{skuId}/kardex', [InventoryController::class, 'kardex'])->name('kardex');
            Route::get('/{skuId}/lots', [InventoryController::class, 'lots'])->name('lots');
            
            Route::post('/transfer-safety', [InventoryController::class, 'transferToSafety'])->name('transfer-safety');
            Route::post('/isolate-quarantine', [InventoryController::class, 'isolateToQuarantine'])->name('isolate-quarantine');
        });
        
        Route::prefix('purchases')->name('purchases.')->group(function () {
            Route::get('/', [PurchaseController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseController::class, 'create'])->name('create');
            Route::post('/', [PurchaseController::class, 'store'])->name('store');
            Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
            Route::put('/{purchase}', [PurchaseController::class, 'complete'])->name('complete');
            Route::post('/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('prices')->name('prices.')->group(function () {
            Route::get('/', [PriceController::class, 'index'])->name('index');
            Route::get('/create', [PriceController::class, 'create'])->name('create');
            Route::post('/', [PriceController::class, 'store'])->name('store');
            Route::delete('/{price}', [PriceController::class, 'destroy'])->name('destroy');
        });

        Route::resource('market-zones', MarketZoneController::class)->parameters(['market-zones' => 'market_zone']);
        
        Route::get('bundles/{id}/items', [BundleController::class, 'items'])->name('bundles.items');
        Route::resource('bundles', BundleController::class)->except(['show']);
        
        Route::get('transfers', fn() => 'Lista de transferencias en desarrollo')->name('transfers.index');
        Route::get('transfers/{id}/receive', fn() => 'Formulario de recepción en desarrollo')->name('transfers.receive');
        Route::post('transfers/{id}/reception', fn() => 'Procesamiento de recepción en desarrollo')->name('transfers.reception');

        Route::prefix('removals')->name('removals.')->group(function () {
            Route::get('/', [RemovalController::class, 'index'])->name('index');
            Route::get('/create', [RemovalController::class, 'create'])->name('create');
            Route::post('/', [RemovalController::class, 'store'])->name('store');
        });

        Route::get('/logistics/monitor', [MonitorController::class, 'index'])->name('logistics.monitor');
        
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