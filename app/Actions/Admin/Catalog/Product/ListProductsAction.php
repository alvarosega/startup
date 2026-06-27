<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListProductsAction
{
    /**
     * Procesa y sirve el listado plano paginado por cursor protegiendo las firmas del paginador.
     */
    public function execute(Request $request): array
    {
        $search = $request->query('search') ? trim((string) $request->query('search')) : null;
        $category = $request->query('category') ? trim((string) $request->query('category')) : null;
        $brand = $request->query('brand') ? trim((string) $request->query('brand')) : null;
        $status = $request->query('status') ? trim((string) $request->query('status')) : null;

        $paginator = Product::query()
            ->with([
                'brand:id,name', 
                'category:id,name', 
                'skus' => fn($query) => $query->orderBy('sort_order', 'asc')
            ])
            ->withCount(['skus' => fn($query) => $query->where('deleted_epoch', 0)])
            ->when($search, fn(Builder $q) => $this->applyFullTextSearch($q, $search))
            ->when($category, fn(Builder $q) => $q->where('category_id', $category))
            ->when($brand, fn(Builder $q) => $q->where('brand_id', $brand))
            ->when($status, fn(Builder $q) => $this->applyHealthFilter($q, $status))
            ->orderBy('id', 'desc') 
            ->cursorPaginate(15)
            ->withQueryString();

        $mappedItems = array_map(function ($product) {
            return [
                'id'           => (string) $product->id,
                'name'         => (string) $product->name,
                'slug'         => (string) $product->slug,
                'image_path'   => $product->image_path ? (string) $product->image_path : null,
                'is_active'    => (bool) $product->is_active,
                'is_featured'  => (bool) $product->is_featured,
                'is_alcoholic' => (bool) $product->is_alcoholic,
                'sort_order'   => (int) $product->sort_order,
                'skus_count'   => (int) $product->skus_count,
                'brand'        => $product->brand ? [
                    'id'   => (string) $product->brand->id,
                    'name' => (string) $product->brand->name,
                ] : null,
                'category'     => $product->category ? [
                    'id'   => (string) $product->category->id,
                    'name' => (string) $product->category->name,
                ] : null,
                'skus'         => $product->skus->map(fn($sku) => [
                    'id'                => (string) $sku->id,
                    'name'              => (string) $sku->name,
                    'code'              => (string) $sku->code,
                    'base_price'        => (float) $sku->base_price,
                    'conversion_factor' => (float) $sku->conversion_factor,
                    'weight'            => (float) $sku->weight,
                    'is_active'         => (bool) $sku->is_active,
                    'sort_order'        => (int) $sku->sort_order,
                ])->toArray(),
            ];
        }, $paginator->items());

        return [
            'data'  => $mappedItems,
            'next'  => $paginator->nextCursor()?->encode(),
            'prev'  => $paginator->previousCursor()?->encode(),
            // RECTIFICACIÓN: Se extrae la matriz de parámetros directamente de la petición HTTP, mitigando la llamada a métodos inexistentes
            'query' => $request->query(),
        ];
    }

    private function applyFullTextSearch(Builder $query, string $search): void
    {
        $term = "{$search}*";
        $query->where(function (Builder $q) use ($term) {
            $q->whereRaw("MATCH(name) AGAINST(? IN BOOLEAN MODE)", [$term])
              ->orWhereHas('skus', fn($sub) => 
                  $sub->whereRaw("MATCH(name, code) AGAINST(? IN BOOLEAN MODE)", [$term])
              );
        });
    }

    private function applyHealthFilter(Builder $query, string $status): void
    {
        if ($status === 'incomplete') {
            $query->has('skus', '=', 0);
        }
        if ($status === 'complete') {
            $query->has('skus', '>', 0);
        }
    }
}