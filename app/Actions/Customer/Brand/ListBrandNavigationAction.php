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
        return Cache::remember('customer_brand_navigation_global', 3600, function () {
            return Brand::query()
                ->select(['id', 'name', 'slug', 'image_path'])
                ->active()
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }
}