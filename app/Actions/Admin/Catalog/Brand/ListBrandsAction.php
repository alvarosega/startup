<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBrandsAction
{
    /**
     * Transforma y sirve datos nativos planos con paginación canónica LengthAwarePaginator.
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $search = isset($filters['search']) && is_string($filters['search']) ? trim($filters['search']) : null;
        $providerId = isset($filters['provider_id']) && is_string($filters['provider_id']) ? trim($filters['provider_id']) : null;
        $categoryId = isset($filters['category_id']) && is_string($filters['category_id']) ? trim($filters['category_id']) : null;

        $paginator = Brand::query()
            ->select([
                'id', 'name', 'slug', 'provider_id', 'category_id', 
                'image_path', 'is_active', 'is_featured', 'sort_order'
            ])
            ->with([
                'provider:id,commercial_name', 
                'category:id,name'
            ])
            ->when($search, fn($q, $s) => $q->where('name', 'like', "{$s}%"))
            ->when($providerId, fn($q, $id) => $q->where('provider_id', $id))
            ->when($categoryId, fn($q, $id) => $q->where('category_id', $id))
            ->orderBy('sort_order')
            ->paginate(30)
            ->withQueryString();

        $paginator->through(function ($brand) {
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
            ];
        });

        return $paginator;
    }
}