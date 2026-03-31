<?php

declare(strict_types=1);

namespace App\Actions\Customer\Featured;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class GetHomeFeaturedAction
{
    public function execute(): Collection
    {
        return Cache::remember(
            'customer_home_featured_top5',
            3600,
            fn () => Product::select(['id', 'name', 'slug', 'image_path'])
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->limit(5)
                ->get()
        );
    }
}