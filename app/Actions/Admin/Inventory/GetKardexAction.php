<?php

namespace App\Actions\Admin\Inventory;

use App\Models\InventoryMovement;
use App\DTOs\Admin\Inventory\KardexFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GetKardexAction
{
    public function execute(KardexFilterDTO $filters, ?string $adminBranchId = null): LengthAwarePaginator
    {
        // El motor de DB calcula el saldo cronológico absoluto (ASC) antes de ordenar la vista (DESC)
        $query = InventoryMovement::query()
            ->select([
                'inventory_movements.*',
                DB::raw('SUM(quantity) OVER (PARTITION BY branch_id, sku_id ORDER BY created_at ASC, id ASC) as running_balance')
            ])
            ->with([
                'admin:id,first_name,last_name',
                'branch:id,name',
                'lot:id,lot_code,expiration_date'
            ])
            ->where('sku_id', $filters->sku_id);

        // Aislamiento de Silo (Zero-Trust)
        if ($adminBranchId) {
            $query->where('branch_id', $adminBranchId);
        } elseif ($filters->branch_id) {
            $query->where('branch_id', $filters->branch_id);
        }

        if ($filters->start_date) {
            $query->whereDate('created_at', '>=', $filters->start_date);
        }

        if ($filters->end_date) {
            $query->whereDate('created_at', '<=', $filters->end_date);
        }

        // Ordenamos DESC para que el usuario vea lo más reciente primero, 
        // pero el 'running_balance' mantendrá el cálculo correcto gracias a la Window Function.
        return $query->orderBy('created_at', 'desc')->paginate($filters->per_page);
    }
}