<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class ListBrands
{
    public function execute(array $filters = []): LengthAwarePaginator
    {
        return Brand::query()
            ->select([
                'id', 'name', 'slug', 'provider_id', 'category_id', 
                'image_path', 'is_active', 'is_featured', 'sort_order'
            ])
            ->with([
                'provider:id,commercial_name', 
                'category:id,name', 
                'marketZones:id,name'
            ])
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('name', 'like', "{$s}%"))
            ->when($filters['provider_id'] ?? null, fn($q, $id) => $q->where('provider_id', $id))
            ->when($filters['category_id'] ?? null, fn($q, $id) => $q->where('category_id', $id))
            ->when($filters['market_zone_id'] ?? null, function($q, $id) {
                $q->whereHas('marketZones', fn($sq) => $sq->where('market_zones.id', $id));
            })
            ->orderBy('sort_order')
            ->paginate(30);
    }
}