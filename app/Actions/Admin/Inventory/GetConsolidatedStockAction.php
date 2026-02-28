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
        // 1. Base Query con Agregación SQL
        $query = InventoryLot::query()
            ->select([
                'branch_id',
                'sku_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(reserved_quantity) as total_reserved'),
                // Rango de costos sin promediar (Refleja la realidad FEFO)
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

        // 2. Filtro de Silo de Seguridad (Admin asignado a sucursal)
        if ($adminBranchId) {
            $query->where('branch_id', $adminBranchId);
        } elseif ($filters->branch_id) {
            $query->where('branch_id', $filters->branch_id);
        }

        // 3. Filtro de Búsqueda (Opcional, por EAN o Nombre)
        if ($filters->search) {
            $query->whereHas('sku', function ($q) use ($filters) {
                $q->where('code', 'like', "%{$filters->search}%")
                  ->orWhere('name', 'like', "%{$filters->search}%")
                  ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$filters->search}%"));
            });
        }

        return $query->paginate($filters->per_page);
    }
}