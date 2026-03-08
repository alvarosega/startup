<?php

namespace App\Actions\Customer\Shop;

use App\Models\Sku;
use App\Models\InventoryLot;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetShopCatalogAction
{
    public function execute(CatalogQueryDTO $dto): LengthAwarePaginator
    {
        return Sku::query()
            ->select('skus.*')
            // 1. Calculamos el stock exacto del silo (Sucursal) directamente en SQL
            ->addSelect([
                'available_stock' => InventoryLot::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                    ->whereColumn('sku_id', 'skus.id')
                    ->where('branch_id', $dto->branchId)
                    ->where('is_safety_stock', false)
            ])
            ->with(['product.brand', 'prices' => function($q) use ($dto) {
                $q->where('branch_id', $dto->branchId)
                  ->where('valid_from', '<=', now())
                  ->where(fn($inner) => $inner->whereNull('valid_to')->orWhere('valid_to', '>', now()));
            }])
            ->whereHas('prices', fn($q) => $q->where('branch_id', $dto->branchId))
            // 2. Filtramos para traer solo SKUs con stock real positivo en esa sucursal
            ->whereHas('inventoryLots', function($q) use ($dto) {
                $q->where('branch_id', $dto->branchId)
                  ->where('is_safety_stock', false)
                  ->whereRaw('(quantity - reserved_quantity) > 0');
            })
            ->when($dto->search, function($q, $term) {
                $q->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$term}%"))
                  ->orWhere('name', 'like', "%{$term}%");
            })
            // 3. Nota Técnica: Si cambiaste la navegación a Marcas, 
            // asegúrate de que tu DTO y este where usen brand_id en lugar de category_id.
            ->when($dto->categoryId, function($q, $catId) {
                $q->whereHas('product', fn($pq) => $pq->where('category_id', $catId));
            })
            ->paginate(20);
    }
}