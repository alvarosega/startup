<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;

class ListProductsAction
{
    public function execute(Request $request): CursorPaginator
    {
        return Product::query()
            ->with([
                'brand:id,name', 
                'category:id,name', 
                'skus' => fn($query) => $query->orderBy('sort_order', 'asc')
            ])
            ->withCount(['skus' => fn($query) => $query->where('deleted_epoch', 0)])
            ->when($request->search, fn(Builder $q, $s) => $this->applyFullTextSearch($q, (string) $s))
            ->when($request->category, fn(Builder $q, $c) => $q->where('category_id', $c))
            ->when($request->brand, fn(Builder $q, $b) => $q->where('brand_id', $b))
            ->when($request->status, fn(Builder $q, $st) => $this->applyHealthFilter($q, (string) $st))
            ->orderBy('id', 'desc') 
            ->cursorPaginate(15)
            ->withQueryString();
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