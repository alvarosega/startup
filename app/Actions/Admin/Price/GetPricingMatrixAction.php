<?php

namespace App\Actions\Admin\Price;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPricingMatrixAction
{
    public function execute(Request $request): LengthAwarePaginator
    {
        return Product::query()
            ->with(['skus.prices' => function($q) {
                $q->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', now()))
                  ->orderBy('priority', 'desc');
            }])
            ->when($request->search, function($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('skus', fn($sub) => $sub->where('code', 'like', "%{$s}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }
}