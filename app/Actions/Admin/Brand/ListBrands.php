<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class ListBrands
{
    public function execute(array $filters = []): LengthAwarePaginator
    {
        return Brand::query()
            ->with([
                'provider:id,commercial_name', 
                'category:id,name', 
                'marketZone:id,name'
            ])
            // LA LEY: Implementación explícita de filtros
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($filters['provider_id'] ?? null, fn($q, $id) => $q->where('provider_id', $id))
            ->when($filters['category_id'] ?? null, fn($q, $id) => $q->where('category_id', $id))
            ->when($filters['market_zone_id'] ?? null, fn($q, $id) => $q->where('market_zone_id', $id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();
    }
}