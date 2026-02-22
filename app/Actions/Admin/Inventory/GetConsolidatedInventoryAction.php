<?php

namespace App\Actions\Admin\Inventory;

use App\DTOs\Admin\Inventory\InventoryFilterDTO;
use App\Models\InventoryLot;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class GetConsolidatedInventoryAction
{
    public function execute(InventoryFilterDTO $filters): CursorPaginator
    {
        $query = InventoryLot::query()
            ->select(
                'branch_id',
                'sku_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(reserved_quantity) as total_reserved'),
                DB::raw('SUM(quantity * unit_cost) / NULLIF(SUM(quantity), 0) as avg_cost')
            )
            ->with([
                'branch:id,name',
                'sku:id,product_id,name,code',
                'sku.product:id,brand_id,name',
                'sku.product.brand:id,name'
            ])
            ->groupBy('branch_id', 'sku_id');

        if ($filters->branch_id) {
            $query->where('branch_id', $filters->branch_id);
        }

        if ($filters->search) {
            $query->whereHas('sku', fn($q) => 
                $q->where('name', 'like', "%{$filters->search}%")
                  ->orWhere('code', 'like', "%{$filters->search}%")
            );
        }

        // Ordenar por ID para Cursor Pagination O(1)
        return $query->orderBy('sku_id')->cursorPaginate($filters->per_page);
    }
}