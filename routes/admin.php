<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\Auth\LoginController;
use App\Http\Controllers\Web\Admin\User\CustomerController;
use App\Http\Controllers\Web\Admin\Driver\DriverController;
use App\Http\Controllers\Web\Admin\Branch\BranchController;
use App\Http\Controllers\Web\Admin\Product\ProductController;
use App\Http\Controllers\Web\Admin\Sku\SkuController;
use App\Http\Controllers\Web\Admin\Category\CategoryController;
use App\Http\Controllers\Web\Admin\MarketZone\MarketZoneController;
use App\Http\Controllers\Web\Admin\Bundle\BundleController;
use App\Http\Controllers\Web\Admin\Brand\BrandController;
use App\Http\Controllers\Web\Admin\Provider\ProviderController;
use App\Http\Controllers\Web\Admin\Price\PriceController;
use App\Http\Controllers\Web\Admin\Inventory\InventoryController;
use App\Http\Controllers\Web\Admin\Inventory\PurchaseIntakeController;
use App\Http\Controllers\Web\Admin\Inventory\PurchaseIntakeViewController;
use App\Http\Controllers\Web\Admin\Inventory\TransferReceptionController;
use App\Http\Controllers\Web\Admin\Inventory\TransferReceptionViewController;
use App\Http\Controllers\Web\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Web\Admin\Logistics\MonitorController;
use App\Http\Controllers\Web\Admin\Order\OrderController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCreativeController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCampaignController;

Route::middleware(['guest:super_admin'])->group(function () {
    Route::get('login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.store');
});

Route::middleware(['auth.admin'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::middleware('role:super_admin,super_admin')->group(function () {
        Route::post('users/validate-step-1', [CustomerController::class, 'validateStep1'])->name('users.validate-step-1');
        Route::resource('users', CustomerController::class);
        Route::post('users/identify-branch', [CustomerController::class, 'identifyBranch'])->name('users.identify-branch');
        
        Route::get('drivers/{driver}/documents/{path}', [DriverController::class, 'showDocument'])
            ->where('path', '.*')
            ->name('drivers.documents.show');

        Route::resource('drivers', DriverController::class);
        Route::resource('branches', BranchController::class);
        
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
        
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('{category}/skus', [CategoryController::class, 'skus'])->name('skus');
            Route::put('{category}/sku-order', [CategoryController::class, 'updateSkuOrder'])->name('update-sku-order');
        });
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('market-zones', MarketZoneController::class)->parameters(['market-zones' => 'market_zone']);
        Route::resource('bundles', BundleController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('providers', ProviderController::class);
        
        Route::prefix('prices')->name('prices.')->group(function () {
            Route::get('/', [PriceController::class, 'index'])->name('index'); // <--- INYECTAR ESTA LÍNEA
            Route::get('{sku}', [PriceController::class, 'show'])->name('show');
            Route::post('/', [PriceController::class, 'store'])->name('store');
            Route::delete('{price}', [PriceController::class, 'destroy'])->name('destroy');
        });
        
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/search', [InventoryController::class, 'search'])->name('inventory.search');

        // CORRECCIÓN: Cambiar {sku} por {skuId}
        Route::get('inventory/{skuId}/kardex', [InventoryController::class, 'kardex'])->name('inventory.kardex');
        Route::get('inventory/{skuId}/lots', [InventoryController::class, 'lots'])->name('inventory.lots');
        // --- NÚCLEO DE ABASTECIMIENTO (Ingreso por Compras) ---
        Route::get('purchases', [PurchaseIntakeViewController::class, 'index'])->name('purchases.index');
        Route::post('purchases/process', PurchaseIntakeController::class)->name('purchases.process');

        Route::get('transfers', fn() => 'Lista de transferencias en desarrollo')->name('transfers.index');
        Route::get('transfers/{id}/receive', [TransferReceptionViewController::class, 'index'])->name('transfers.receive');
        Route::post('transfers/{id}/reception', TransferReceptionController::class)->name('transfers.reception');

        // LEY: Agregar de nuevo estas dos líneas que se borraron en la reestructuración previa
        Route::get('removals', fn() => 'En desarrollo')->name('removals.index');
        Route::get('transformations', fn() => 'En desarrollo')->name('transformations.index');

        Route::get('/logistics/monitor', [MonitorController::class, 'index'])->name('logistics.monitor');
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index'); 
            Route::get('/{order:code}', [OrderController::class, 'show'])->name('show'); 
            Route::get('/{order:code}/proof', [OrderController::class, 'showProof'])->name('show-proof');
            Route::post('/{order:code}/approve-payment', [OrderController::class, 'approvePayment'])->name('approve-payment');
            Route::post('/{order:code}/reject-payment', [OrderController::class, 'rejectPayment'])->name('reject-payment');
            Route::post('/{order:code}/ready', [OrderController::class, 'markAsReady'])->name('mark-as-ready');
            Route::post('/{order:code}/dispatch', [OrderController::class, 'dispatchOrder'])->name('dispatch');
            Route::post('/{order:code}/unassign', [OrderController::class, 'unassignDriver'])->name('unassign-driver');
        });
        
        Route::prefix('retail-media')->name('retail-media.')->group(function () {
            Route::get('ad-creatives/search-skus', [AdCreativeController::class, 'searchSkus'])->name('ad-creatives.search-skus');
            Route::get('ad-creatives/search-bundles', [AdCreativeController::class, 'searchBundles'])->name('ad-creatives.search-bundles');
            Route::get('ad-creatives/search-brands', [AdCreativeController::class, 'searchBrands'])->name('ad-creatives.search-brands');
            
            Route::resource('ad-creatives', AdCreativeController::class)->parameters(['ad-creatives' => 'ad_creative']);
            Route::resource('ad-campaigns', AdCampaignController::class);
        });
    });
});