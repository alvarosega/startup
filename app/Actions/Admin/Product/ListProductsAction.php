<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    public function execute(Request $request): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand', 'category', 'skus'])
            ->withCount('skus')
            ->when($request->search, fn(Builder $q, $s) => $this->applySearch($q, $s))
            ->when($request->category, fn(Builder $q, $c) => $q->where('category_id', $c))
            ->when($request->brand, fn(Builder $q, $b) => $q->where('brand_id', $b))
            ->when($request->status, fn(Builder $q, $st) => $this->applyHealthFilter($q, $st))
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhereHas('skus', fn($sub) => $sub->where('code', 'like', "%{$search}%"));
        });
    }

    private function applyHealthFilter(Builder $query, string $status): void
    {
        if ($status === 'incomplete') $query->has('skus', '=', 0);
        if ($status === 'complete') $query->has('skus', '>', 0);
    }
}