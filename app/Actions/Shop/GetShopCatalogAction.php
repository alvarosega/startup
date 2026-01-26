<?php

namespace App\Actions\Shop;

use App\Models\Product;
use App\DTOs\Shop\CatalogQueryDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetShopCatalogAction
{
    public function execute(CatalogQueryDTO $dto): LengthAwarePaginator
    {
        return Product::query() // <--- ASEGURATE QUE ESTE 'return' ESTÉ AQUÍ
            ->with([
                'brand:id,name', 
                'category:id,name',
                'skus' => function ($query) use ($dto) {
                    $query->where('is_active', true)
                        ->with(['prices' => function ($q) use ($dto) {
                            $q->where('branch_id', $dto->branchId)
                              ->orWhereNull('branch_id');
                        }])
                        ->with(['inventoryLots' => function ($q) use ($dto) {
                            $q->where('branch_id', $dto->branchId);
                        }]);
                }
            ])
            ->where('is_active', true)
            ->when($dto->search, function (Builder $q, $term) {
                $q->where(function ($subQ) use ($term) {
                    $subQ->where('name', 'like', "%{$term}%")
                         ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($dto->categoryId, function (Builder $q, $id) {
                $q->where('category_id', $id);
            })
            ->when($dto->inStockOnly, function (Builder $q) use ($dto) {
                $q->whereHas('skus.inventoryLots', function ($lot) use ($dto) {
                    $lot->where('branch_id', $dto->branchId)
                        ->havingRaw('SUM(quantity - reserved_quantity) > 0');
                });
            })
            ->orderBy('name')
            ->paginate($dto->perPage);
    }
}