<?php

namespace App\Actions\Admin\Inventory\Stock;

use App\DTOs\Admin\Inventory\Stock\StockFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GetConsolidatedStockAction {
    public function execute(StockFilterDTO $filters, ?string $adminBranchId = null): LengthAwarePaginator {
        $query = DB::table('inventory_balances as ib')
            ->select([
                'ib.branch_id', 
                'ib.sku_id', 
                'ib.total_physical as total_quantity', // Alias sincronizado con Resource
                'ib.total_reserved', 
                'ib.total_safety as safety_quantity', // Alias sincronizado con Resource
                's.name as sku_name', 
                's.code as sku_code',
                'b.name as branch_name',
                'p.name as product_name',
                'br.name as brand_name',
                // Subconsulta para costos (FEFO) - Evita N+1
                DB::raw("(SELECT MIN(unit_cost) FROM inventory_lots WHERE sku_id = ib.sku_id AND branch_id = ib.branch_id AND quantity > 0) as min_cost"),
                DB::raw("(SELECT MAX(unit_cost) FROM inventory_lots WHERE sku_id = ib.sku_id AND branch_id = ib.branch_id AND quantity > 0) as max_cost")
            ])
            ->join('skus as s', 'ib.sku_id', '=', 's.id')
            ->join('products as p', 's.product_id', '=', 'p.id')
            ->join('brands as br', 'p.brand_id', '=', 'br.id')
            ->join('branches as b', 'ib.branch_id', '=', 'b.id');

        // Seguridad de Silo
        if ($adminBranchId) {
            $query->where('ib.branch_id', $adminBranchId);
        } elseif ($filters->branch_id) {
            $query->where('ib.branch_id', $filters->branch_id);
        }

        // Filtros por Categoría/Marca/Proveedor (Si se requieren joins adicionales)
        if ($filters->category_id) $query->where('p.category_id', $filters->category_id);
        if ($filters->brand_id) $query->where('p.brand_id', $filters->brand_id);

        if ($filters->search) {
            $query->where(fn($q) => 
                $q->where('s.code', 'like', "{$filters->search}%")
                  ->orWhere('s.name', 'like', "%{$filters->search}%")
                  ->orWhere('p.name', 'like', "%{$filters->search}%")
            );
        }

        return $query->orderBy('b.name')->orderBy('p.name')->paginate($filters->per_page);
    }
}