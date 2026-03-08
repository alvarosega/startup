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
            ->with(['skus' => function($q) {
                $q->with(['prices' => function($sub) {
                    // Only fetch currently active prices
                    $sub->where(fn($p) => $p->whereNull('valid_to')->orWhere('valid_to', '>=', now()))
                        ->orderBy('branch_id')
                        ->orderByDesc('priority'); // Highest priority first
                }]);
            }])
            ->when($request->search, function($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('skus', fn($sub) => $sub->where('code', 'like', "%{$s}%"));
            })
            // If branch is selected, filter products that have SKUs with prices in that branch
            ->when($request->branch_id, function($q, $bId) {
                 $q->whereHas('skus.prices', fn($sub) => $sub->where('branch_id', $bId));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }
}