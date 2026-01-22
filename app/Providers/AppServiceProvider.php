<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

// 1. IMPORTAR MODELOS
use App\Models\Category;
use App\Models\Provider;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Sku; // <--- FALTABA ESTE
use App\Models\InventoryLot;
use App\Models\Transfer;
use App\Models\RemovalRequest;

// 2. IMPORTAR POLICIES (NECESARIO PARA REGISTRO MANUAL)
use App\Policies\CategoryPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\BrandPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SkuPolicy;

// 3. IMPORTAR OBSERVERS
use App\Observers\CategoryObserver;
use App\Observers\ProviderObserver;
use App\Observers\BrandObserver;
use App\Observers\ProductObserver;
use App\Observers\InventoryLotObserver;
use App\Observers\TransferObserver;
use App\Observers\RemovalRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Fix longitud strings para MySQL antiguos
        Schema::defaultStringLength(191);

        // --- GATES Y POLICIES ---

        // 1. Gate Global para Super Admin (GOD MODE)
        // Esto asegura que el Super Admin pase TODAS las validaciones antes de revisar las policies
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // 2. Registro Manual de Policies (Blindaje contra fallos de auto-discovery)
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Provider::class, ProviderPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Sku::class, SkuPolicy::class);

        // 3. Gate Legacy para Dashboard
        Gate::define('view_admin_dashboard', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'branch_admin', 'logistics_manager', 'inventory_manager', 'finance_manager']);
        });

        // --- OBSERVERS ---
        Category::observe(CategoryObserver::class);
        Provider::observe(ProviderObserver::class);
        Brand::observe(BrandObserver::class);
        Product::observe(ProductObserver::class);
        InventoryLot::observe(InventoryLotObserver::class);
        Transfer::observe(TransferObserver::class);
        RemovalRequest::observe(RemovalRequestObserver::class);
    }
}