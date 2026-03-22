<?php

namespace App\Actions\Customer\Shop;

use App\Models\Sku;
use App\Models\InventoryLot;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class GetShopCatalogAction
{
    public function execute(CatalogQueryDTO $dto): LengthAwarePaginator
    {
        return Sku::query()
            ->select('skus.*')
            // Pilar 3.A: Stock calculado en SQL (Zero-Latency)
            ->addSelect([
                'available_stock' => InventoryLot::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $dto->branchId)
                    ->where('is_safety_stock', false)
            ])
            ->with(['product.brand', 'prices' => function($q) use ($dto) {
                $q->where('branch_id', $dto->branchId)
                  ->where('valid_from', '<=', now())
                  ->where(fn($i) => $i->whereNull('valid_to')->orWhere('valid_to', '>', now()));
            }])
            ->whereHas('prices', fn($q) => $q->where('branch_id', $dto->branchId))
            ->when($dto->search, function($q, $term) {
                $q->where(fn($sub) => 
                    $sub->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$term}%"))
                        ->orWhere('name', 'like', "%{$term}%")
                );
            })
            ->when($dto->categoryId, function($q, $catId) {
                $q->whereHas('product', fn($pq) => $pq->where('category_id', $catId));
            })
            ->paginate(24);
    }
}