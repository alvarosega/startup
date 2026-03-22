<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\Relation;

// Modelos
use App\Models\Admin;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Transfer;

// Policies
use App\Policies\CategoryPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\BrandPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SkuPolicy;
use App\Policies\TransferPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (config('app.env') !== 'testing') {
            // 1. Fuerza al generador de rutas a usar HTTPS
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // 2. EL BLINDAJE: Engaña al objeto Request global para que el Paginador crea que está en HTTPS
            request()->server->set('HTTPS', 'on');
        }

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // --- GATES ---
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Gate::define('view_admin_dashboard', function (Admin $user) {
            return $user->hasAnyRole(['super_admin', 'branch_admin', 'logistics_manager', 'inventory_manager', 'finance_manager']);
        });

        // --- POLICIES ---
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Provider::class, ProviderPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Sku::class, SkuPolicy::class);
        Gate::policy(Transfer::class, TransferPolicy::class);

        // --- AUTH PROVIDERS ---
        // Nota: Generalmente esto no es necesario si config/auth.php está bien, 
        // pero lo dejamos si te funciona.
        Auth::provider('admins', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], $config['model']);
        });
        Auth::provider('drivers', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], $config['model']);
        });
        Relation::morphMap([
            'sku'    => \App\Models\Sku::class,
            'bundle' => \App\Models\Bundle::class,
        ]);
    }
}