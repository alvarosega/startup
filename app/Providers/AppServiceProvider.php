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
use App\Models\InventoryLot;
use App\Models\Transfer;

use App\Models\RemovalRequest;




// 2. IMPORTAR OBSERVERS
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

        // Definición de Gates
        Gate::define('view_admin_dashboard', function (User $user) {
            return $user->roles->contains('name', 'branch_admin') || 
                   $user->roles->contains('name', 'super_admin');
        });

        // 3. REGISTRO DE OBSERVERS (Faltaban Provider y Brand)
        Category::observe(CategoryObserver::class);
        Provider::observe(ProviderObserver::class); // <--- FALTABA
        Brand::observe(BrandObserver::class);       // <--- FALTABA
        Product::observe(ProductObserver::class);
        InventoryLot::observe(InventoryLotObserver::class);
        Transfer::observe(TransferObserver::class);
        RemovalRequest::observe(RemovalRequestObserver::class);
      
        
                
    }
}