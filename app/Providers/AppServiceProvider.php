<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // <--- FALTABA ESTA
use Illuminate\Support\Facades\Log; // <--- FALTABA ESTA

// Importaciones de modelos
use App\Models\Admin;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Transfer;
use App\Models\Branch;

// Importaciones de Policies
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
        // --- SOLUCIÓN PARA CLOUD WORKSTATIONS ---
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

        // --- AUTH PROVIDER CUSTOM ---
        Auth::provider('admins', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], $config['model']);
        });

        // --- CHIVATO DE SQL (DEBUG) ---
        DB::listen(function ($query) {
            // Solo loguear consultas a la tabla admins para no saturar el log
            if (str_contains($query->sql, 'admins')) {
                Log::info('SQL ADMIN DEBUG:', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings, // ¡Aquí veremos si envía HEX o BINARIO!
                    'time' => $query->time
                ]);
            }
        });
    }
}