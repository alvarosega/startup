<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
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
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('name', 'like', "{$s}%")) // Solo prefijo para usar índice
            ->when($filters['category_id'] ?? null, fn($q, $id) => $q->where('category_id', $id))
            ->when($filters['market_zone_id'] ?? null, function($q, $id) {
                $q->whereHas('marketZones', fn($sq) => $sq->where('market_zones.id', $id));
            })
            ->orderBy('sort_order')
            ->paginate(30);
    }
}