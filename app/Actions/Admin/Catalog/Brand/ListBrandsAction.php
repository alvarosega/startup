<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;

class ListBrandsAction
{
    /**
     * RECTIFICACIÓN: Transforma y sirve datos nativos planos erradicando el anidamiento intrusivo de JsonResources.
     */
    public function execute(array $filters = []): array
    {
        $search = isset($filters['search']) && is_string($filters['search']) ? trim($filters['search']) : null;
        $providerId = isset($filters['provider_id']) && is_string($filters['provider_id']) ? trim($filters['provider_id']) : null;
        $categoryId = isset($filters['category_id']) && is_string($filters['category_id']) ? trim($filters['category_id']) : null;
        $marketZoneId = isset($filters['market_zone_id']) && is_string($filters['market_zone_id']) ? trim($filters['market_zone_id']) : null;

        $paginator = Brand::query()
            ->select([
                'id', 'name', 'slug', 'provider_id', 'category_id', 
                'image_path', 'is_active', 'is_featured', 'sort_order'
            ])
            ->with([
                'provider:id,commercial_name', 
                'category:id,name', 
                'marketZones:id,name'
            ])
            ->when($search, fn($q, $s) => $q->where('name', 'like', "{$s}%"))
            ->when($providerId, fn($q, $id) => $q->where('provider_id', $id))
            ->when($categoryId, fn($q, $id) => $q->where('category_id', $id))
            ->when($marketZoneId, function($q, $id) {
                $q->whereHas('marketZones', fn($sq) => $sq->where('market_zones.id', $id));
            })
            ->orderBy('sort_order')
            ->paginate(30);

        $mappedItems = array_map(function ($brand) {
            return [
                'id'          => (string) $brand->id,
                'name'        => (string) $brand->name,
                'slug'        => (string) $brand->slug,
                'image_path'  => $brand->image_path ? (string) $brand->image_path : null,
                'is_active'   => (bool) $brand->is_active,
                'is_featured' => (bool) $brand->is_featured,
                'sort_order'  => (int) $brand->sort_order,
                'provider'    => $brand->provider ? [
                    'id'              => (string) $brand->provider->id,
                    'commercial_name' => (string) $brand->provider->commercial_name
                ] : null,
                'category'    => $brand->category ? [
                    'id'   => (string) $brand->category->id,
                    'name' => (string) $brand->category->name
                ] : null,
                'market_zones' => $brand->marketZones->map(fn($zone) => [
                    'id'   => (string) $zone->id,
                    'name' => (string) $zone->name
                ])->toArray()
            ];
        }, $paginator->items());

        return [
            'data' => $mappedItems,
            'links' => [
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ]
        ];
    }
}