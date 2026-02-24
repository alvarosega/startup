<?php

namespace App\Actions\Customer\Shop;

use App\Models\Sku;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetShopCatalogAction
{
    public function execute(CatalogQueryDTO $dto): LengthAwarePaginator
    {
        return Sku::query()
            ->with(['product.brand', 'prices' => function($q) use ($dto) {
                $q->where('branch_id', $dto->branchId)
                  ->where('valid_from', '<=', now())
                  ->where(fn($inner) => $inner->whereNull('valid_to')->orWhere('valid_to', '>', now()));
            }])
            ->whereHas('prices', fn($q) => $q->where('branch_id', $dto->branchId))
            ->whereHas('inventoryLots', fn($q) => $q->where('branch_id', $dto->branchId)->where('quantity', '>', 0))
            ->when($dto->search, function($q, $term) {
                $q->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$term}%"))
                  ->orWhere('name', 'like', "%{$term}%");
            })
            ->when($dto->categoryId, function($q, $catId) {
                $q->whereHas('product', fn($pq) => $pq->where('category_id', $catId));
            })
            ->paginate(20);
    }
}