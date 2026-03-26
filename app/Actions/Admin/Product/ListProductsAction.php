<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;

class ListProductsAction
{
    public function execute(Request $request): CursorPaginator
    {
        return Product::query()
            ->select(['id', 'brand_id', 'category_id', 'name', 'slug', 'image_path', 'is_active'])
            ->with([
                'brand:id,name', 
                'category:id,name', 
                'skus:id,product_id,name,code,base_price,image_path,is_active,weight'
            ])
            ->withCount('skus')
            ->when($request->search, fn(Builder $q, $s) => $this->applyMilitarSearch($q, $s))
            ->when($request->category, fn(Builder $q, $c) => $q->where('category_id', $c))
            ->when($request->brand, fn(Builder $q, $b) => $q->where('brand_id', $b))
            ->when($request->status, fn(Builder $q, $st) => $this->applyHealthFilter($q, $st))
            ->orderBy('id', 'desc') // Orden natural UUIDv7
            ->cursorPaginate(15)
            ->withQueryString();
    }

    /**
     * Motor de búsqueda de Grado Militar (Full-Text Search)
     */
    private function applyMilitarSearch(Builder $query, string $search): void
    {
        $term = "{$search}*"; // Operador de prefijo para búsqueda parcial

        $query->where(function (Builder $q) use ($term) {
            // MATCH AGAINST en el Maestro
            $q->whereRaw("MATCH(name) AGAINST(? IN BOOLEAN MODE)", [$term])
              // MATCH AGAINST en las Variantes (Silos relacionados)
              ->orWhereHas('skus', fn($sub) => 
                  $sub->whereRaw("MATCH(name, code) AGAINST(? IN BOOLEAN MODE)", [$term])
              );
        });
    }

    private function applyHealthFilter(Builder $query, string $status): void
    {
        if ($status === 'incomplete') $query->has('skus', '=', 0);
        if ($status === 'complete') $query->has('skus', '>', 0);
    }
}