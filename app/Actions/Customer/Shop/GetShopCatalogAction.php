<?php

namespace App\Actions\Customer\Shop;

use App\Models\Sku;
use App\Models\InventoryBalance;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetShopCatalogAction
{
    public function execute(CatalogQueryDTO $dto): LengthAwarePaginator
    {
        return Sku::query()
            ->select('skus.*')
            // LEY: Consistencia absoluta de inventario disponible comercializable
            ->addSelect([
                'available_stock' => InventoryBalance::selectRaw('COALESCE(total_physical - total_reserved - total_quarantine, 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $dto->branchId)
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