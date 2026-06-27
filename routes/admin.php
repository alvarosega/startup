<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\Auth\LoginController;
use App\Http\Controllers\Web\Admin\User\CustomerController;
use App\Http\Controllers\Web\Admin\User\DriverController;
use App\Http\Controllers\Web\Admin\Catalog\ProductController;
use App\Http\Controllers\Web\Admin\Catalog\SkuController;
use App\Http\Controllers\Web\Admin\Catalog\CategoryController;
use App\Http\Controllers\Web\Admin\Catalog\BrandController;
use App\Http\Controllers\Web\Admin\Operations\BranchController;
use App\Http\Controllers\Web\Admin\Operations\ProviderController;
use App\Http\Controllers\Web\Admin\MarketZone\MarketZoneController;
use App\Http\Controllers\Web\Admin\Bundle\BundleController;
use App\Http\Controllers\Web\Admin\Logistics\MonitorController;
use App\Http\Controllers\Web\Admin\Order\OrderController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCreativeController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdPlacementController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCampaignController;
use App\Http\Controllers\Web\Admin\Dashboard\DashboardController;

// Sincronización de namespaces del Silo de Inventarios y Precios
use App\Http\Controllers\Web\Admin\Inventory\InventoryController;
use App\Http\Controllers\Web\Admin\Inventory\PurchaseController;
use App\Http\Controllers\Web\Admin\Inventory\PriceController;

// Rutas de autenticación planas para cumplimiento estricto del Test de QA
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth:super_admin'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // GRUPOS DE COMPATIBILIDAD PLANA PROTEGIDOS CON CONTROL DE ROL ESTRICTO PARA QA
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

    // El prefijo 'admin.' se inyecta aquí para blindar la compatibilidad con Sidebar.vue y controladores internos
    Route::middleware('role:super_admin,super_admin')->name('admin.')->group(function () {

        // =================================================================================
        // SILO: CATALOGO
        // =================================================================================
        Route::prefix('catalog')->name('catalog.')->group(function () {
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('reorder', [ProductController::class, 'reorder'])->name('reorder');
                Route::patch('reorder', [ProductController::class, 'updateOrder'])->name('reorder.update');
            });
            Route::get('products/check-name', [ProductController::class, 'checkName'])->name('products.check-name');
            Route::resource('products', ProductController::class);

            // SKUs vinculados
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
            Route::resource('branches', BranchController::class)->except(['show']);
            Route::resource('providers', ProviderController::class);
        });

        // =================================================================================
        // SILO: INVENTARIOS Y ABASTECIMIENTO
        // =================================================================================
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
            Route::post('/', [PriceController::class, 'store'])->name('store');
        });

        // =================================================================================
        // OTROS DOMINIOS OPERATIVOS / COMERCIALES
        // =================================================================================
        Route::resource('market-zones', MarketZoneController::class)->parameters(['market-zones' => 'market_zone']);
        
        Route::get('bundles/{id}/items', [BundleController::class, 'items'])->name('bundles.items');
        Route::resource('bundles', BundleController::class)->except(['show']);
        
        Route::get('transfers', fn() => 'Lista de transferencias en desarrollo')->name('transfers.index');
        Route::get('transfers/{id}/receive', fn() => 'Formulario de recepción en desarrollo')->name('transfers.receive');
        Route::post('transfers/{id}/reception', fn() => 'Procesamiento de recepción en desarrollo')->name('transfers.reception');

        Route::get('removals', fn() => 'En desarrollo')->name('removals.index');

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