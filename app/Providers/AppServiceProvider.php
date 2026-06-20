<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\Category;
use App\Models\Provider;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Transfer;
use App\Models\Purchase; // INYECTADO

use App\Policies\CategoryPolicy;
use App\Policies\ProviderPolicy;
use App\Policies\BrandPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SkuPolicy;
use App\Policies\TransferPolicy;
use App\Policies\PurchasePolicy; // INYECTADO

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (config('app.env') !== 'testing') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // LEY: Validar el rol pasando el guard explícito para evitar lecturas sobre el guard 'web' por defecto
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('super_admin', 'super_admin') ? true : null;
        });

        // --- MAPEO EXPLÍCITO DE POLICIES ---
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Provider::class, ProviderPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Sku::class, SkuPolicy::class);
        Gate::policy(Transfer::class, TransferPolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class); // INYECTADO CORRECCIÓN

        Relation::morphMap([
            'sku'    => \App\Models\Sku::class,
            'bundle' => \App\Models\Bundle::class,
        ]);
    }
}