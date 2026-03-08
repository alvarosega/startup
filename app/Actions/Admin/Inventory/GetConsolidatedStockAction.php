<?php

namespace App\Actions\Admin\Inventory;

use App\Models\InventoryLot;
use App\DTOs\Admin\Inventory\StockFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GetConsolidatedStockAction
{
    public function execute(StockFilterDTO $filters, ?string $adminBranchId = null): LengthAwarePaginator
    {
        $query = InventoryLot::query()
            ->select([
                'branch_id',
                'sku_id',
                DB::raw('SUM(quantity) as total_quantity'),
                // Separación matemática en base de datos (Compatible SQLite y MySQL 8+)
                DB::raw('SUM(CASE WHEN is_safety_stock = 1 THEN quantity ELSE 0 END) as safety_quantity'),
                DB::raw('SUM(CASE WHEN is_safety_stock = 0 THEN quantity ELSE 0 END) as normal_quantity'),
                DB::raw('SUM(reserved_quantity) as total_reserved'),
                DB::raw('MIN(unit_cost) as min_cost'),
                DB::raw('MAX(unit_cost) as max_cost'),
            ])
            ->with([
                'branch:id,name',
                'sku:id,product_id,code,name',
                'sku.product:id,brand_id,name',
                'sku.product.brand:id,name'
            ])
            ->groupBy('branch_id', 'sku_id');

        if ($adminBranchId) {
            $query->where('branch_id', $adminBranchId);
        } elseif ($filters->branch_id) {
            $query->where('branch_id', $filters->branch_id);
        }

        if ($filters->search) {
            $query->whereHas('sku', function ($q) use ($filters) {
                $q->where('code', 'like', "%{$filters->search}%")
                  ->orWhere('name', 'like', "%{$filters->search}%")
                  ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$filters->search}%"));
            });
        }

        if ($filters->brand_id || $filters->category_id || $filters->provider_id) {
            $query->whereHas('sku.product', function($q) use ($filters) {
                if ($filters->brand_id) $q->where('brand_id', $filters->brand_id);
                if ($filters->category_id) $q->where('category_id', $filters->category_id);
                if ($filters->provider_id) {
                    $q->whereHas('brand', fn($b) => $b->where('provider_id', $filters->provider_id));
                }
            });
        }

        // Filtro Matemático: Solo evalúa el Stock Comercial (Ordinario - Reservado)
        if ($filters->status) {
            $availableCalc = '(SUM(CASE WHEN is_safety_stock = 0 THEN quantity ELSE 0 END) - SUM(reserved_quantity))';
            
            if ($filters->status === 'OUT_OF_STOCK') {
                $query->havingRaw("$availableCalc <= 0");
            } elseif ($filters->status === 'LOW_STOCK') {
                $query->havingRaw("$availableCalc > 0 AND $availableCalc < 10");
            } elseif ($filters->status === 'IN_STOCK') {
                $query->havingRaw("$availableCalc >= 10");
            }
        }

        return $query->paginate($filters->per_page);
    }
}