<?php

declare(strict_types=1);

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
            // 1. ELIMINADO EL SELECT RESTRICTIVO: Es obligatorio traer todas las columnas 
            // (especialmente sort_order) para que cursorPaginate y el GlobalScope no colapsen.
            ->with(['brand', 'category', 'skus']) // 2. Carga completa para evitar fallos de SoftDeletes
            ->withCount('skus')
            ->when($request->search, fn(Builder $q, $s) => $this->applyMilitarSearch($q, $s))
            ->when($request->category, fn(Builder $q, $c) => $q->where('category_id', $c))
            ->when($request->brand, fn(Builder $q, $b) => $q->where('brand_id', $b))
            ->when($request->status, fn(Builder $q, $st) => $this->applyHealthFilter($q, $st))
            ->orderBy('id', 'desc') // Orden secundario UUIDv7
            ->cursorPaginate(15)
            ->withQueryString();
    }

    /**
     * Motor de búsqueda de Grado Militar (Full-Text Search)
     */
    private function applyMilitarSearch(Builder $query, string $search): void
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
        if ($status === 'incomplete') $query->has('skus', '=', 0);
        if ($status === 'complete') $query->has('skus', '>', 0);
    }
}