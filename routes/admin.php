<?php

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
use App\Http\Controllers\Web\Admin\Inventory\PurchaseController;
use App\Http\Controllers\Web\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Web\Admin\Logistics\MonitorController;
use App\Http\Controllers\Web\Admin\Order\OrderController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCreativeController;
use App\Http\Controllers\Web\Admin\RetailMedia\AdCampaignController;

Route::middleware(['guest:super_admin'])->group(function () {
    Route::get('login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.store');
});

Route::middleware(['auth:super_admin'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::middleware('role:super_admin')->group(function () {
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
            Route::get('{category}/sku-order', [CategoryController::class, 'skuOrder'])->name('sku-order');
            Route::patch('{category}/sku-order', [CategoryController::class, 'updateSkuOrder'])->name('sku-order.update');
        });
        Route::resource('categories', CategoryController::class)->except(['show']);
        
        Route::resource('market-zones', MarketZoneController::class)->parameters(['market-zones' => 'market_zone']);
        Route::resource('bundles', BundleController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('providers', ProviderController::class);
        
        Route::get('prices', [PriceController::class, 'index'])->name('prices.index');
        Route::post('prices', [PriceController::class, 'store'])->name('prices.store');

        Route::resource('inventory', InventoryController::class)->only(['index']);
        Route::get('/inventory/stock/{branch}', [InventoryController::class, 'getStockByBranch'])->name('inventory.stock-by-branch');
        Route::get('inventory/search', [InventoryController::class, 'search'])->name('inventory.search');
        Route::get('inventory/{sku}/kardex', [InventoryController::class, 'kardex'])->name('inventory.kardex');
        
        Route::get('purchases/{purchase}/items', [PurchaseController::class, 'items'])->name('purchases.items'); 
        Route::resource('purchases', PurchaseController::class);
        
        Route::get('/logistics/monitor', [MonitorController::class, 'index'])->name('logistics.monitor');

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index'); 
            Route::post('/{id}/approve-payment', [OrderController::class, 'approvePayment'])->name('approve-payment');
            Route::post('/{id}/reject-payment', [OrderController::class, 'rejectPayment'])->name('reject-payment');
            Route::post('/{id}/dispatch', [OrderController::class, 'dispatchOrder'])->name('dispatch');
        });

        Route::prefix('retail-media')->name('retail-media.')->group(function () {
            // Endpoints de búsqueda para el Formulario (Targeting)
            Route::get('ad-creatives/search-skus', [AdCreativeController::class, 'searchSkus'])->name('ad-creatives.search-skus');
            Route::get('ad-creatives/search-bundles', [AdCreativeController::class, 'searchBundles'])->name('ad-creatives.search-bundles');
            Route::get('ad-creatives/search-brands', [AdCreativeController::class, 'searchBrands'])->name('ad-creatives.search-brands');
            
            Route::resource('ad-creatives', AdCreativeController::class)->parameters(['ad-creatives' => 'ad_creative']);
            Route::resource('ad-campaigns', AdCampaignController::class);
        });
        Route::get('transfers', fn() => 'En desarrollo')->name('transfers.index');
        Route::get('removals', fn() => 'En desarrollo')->name('removals.index');
        Route::get('transformations', fn() => 'En desarrollo')->name('transformations.index');
    });
});