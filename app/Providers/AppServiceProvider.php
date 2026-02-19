<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }

        Schema::defaultStringLength(191);

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

        /*// --- DEBUG SQL (LIMPIO) ---
        // Solo activamos esto en modo debug y SIN lógica binaria
        if (config('app.debug')) {
            DB::listen(function ($query) {
                // Filtramos para no llenar el log, solo lo importante
                if (str_contains($query->sql, 'admins') || str_contains($query->sql, 'drivers') || str_contains($query->sql, 'sessions')) {
                    Log::info('SQL AUTH:', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings, // Bindings directos, sin conversión
                        'time' => $query->time
                    ]);
                }
            });
        }*/
    }
}