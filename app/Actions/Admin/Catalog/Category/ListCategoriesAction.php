<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Category;

use App\Models\Catalog\Category;
use App\Models\Catalog\Sku;

class ListCategoriesAction
{
    /**
     * Resuelve de forma centralizada todo el estado de la pantalla de categorías.
     * Utiliza una función anónima nativa para diferir la ejecución de consultas pesadas.
     */
    public function execute(array $filters): array
    {
        $search = isset($filters['search']) && is_string($filters['search']) ? trim($filters['search']) : null;
        $selectedCategory = isset($filters['selected_category']) && is_string($filters['selected_category']) ? trim($filters['selected_category']) : null;
    
        $query = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug', 'is_active', 'is_featured', 'sort_order', 'external_code']);
    
        if ($search !== null && $search !== '') {
            $term = "%{$search}%";
            $query->where(fn($q) => $q->where('name', 'like', $term)->orWhere('external_code', 'like', $term))
                  ->with(['parent:id,name', 'children']);
        } else {
            $query->whereNull('parent_id')->with(['children']);
        }
    
        $paginator = $query->orderBy('sort_order')->paginate(20);

        $mappedCategories = array_map(function ($category) {
            return [
                'id'            => (string) $category->id,
                'parent_id'     => $category->parent_id ? (string) $category->parent_id : null,
                'name'          => (string) $category->name,
                'slug'          => (string) $category->slug,
                'is_active'     => (bool) $category->is_active,
                'is_featured'   => (bool) $category->is_featured,
                'sort_order'    => (int) $category->sort_order,
                'external_code' => $category->external_code ? (string) $category->external_code : null,
                'parent'        => $category->parent ? [
                    'id'   => (string) $category->parent->id,
                    'name' => (string) $category->parent->name
                ] : null,
                'children'      => $category->children->map(fn($child) => [
                    'id'            => (string) $child->id,
                    'parent_id'     => (string) $child->parent_id,
                    'name'          => (string) $child->name,
                    'slug'          => (string) $child->slug,
                    'is_active'     => (bool) $child->is_active,
                    'is_featured'   => (bool) $child->is_featured,
                    'sort_order'    => (int) $child->sort_order,
                    'external_code' => $child->external_code ? (string) $child->external_code : null,
                ])->toArray()
            ];
        }, $paginator->items());

        $parents = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => ['id' => (string) $p->id, 'name' => (string) $p->name])
            ->toArray();

        return [
            'categories' => [
                'data' => $mappedCategories,
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
            ],
            'parents' => $parents,
            'filters' => [
                'search'            => $search,
                'selected_category' => $selectedCategory
            ],
            // RECTIFICACIÓN: Clausura pura. Se ejecuta solo si se solicita o en carga inicial defendida por la sub-función
            'skus' => fn(): array => $this->resolveSkus($selectedCategory),
            'can_manage' => true
        ];
    }

    /**
     * Resuelve los SKUs bajo demanda aplicando defensas de nulidad para no golpear la base de datos de forma innecesaria.
     */
    private function resolveSkus(?string $selectedCategory): array
    {
        if ($selectedCategory === null || $selectedCategory === '') {
            return [];
        }

        return Sku::query()
            ->whereHas('product', fn($q) => $q->where('category_id', $selectedCategory))
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($sku) => [
                'id'         => (string) $sku->id,
                'name'       => (string) ($sku->name ?? $sku->product?->name),
                'sort_order' => (int) $sku->sort_order,
                'product'    => $sku->product ? [
                    'id'   => (string) $sku->product->id,
                    'name' => (string) $sku->product->name
                ] : null
            ])->toArray();
    }
}