<?php

declare(strict_types=1);

namespace App\Actions\Customer\Brand;

use App\Models\Brand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ListBrandNavigationAction
{
    public function execute(): Collection
    {
        // LEY DE CAMBIO: Debes limpiar la caché con 'php artisan cache:forget customer_brand_navigation_global'
        return Cache::remember('customer_brand_navigation_global', 3600, function () {
            return Brand::query()
                ->select(['id', 'name', 'slug', 'image_path', 'bg_color']) // <--- AGREGADO bg_color
                ->active()
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }
}