<?php

namespace App\Actions\Admin\Inventory\Stock;

use App\Models\InventoryMovement;
use App\DTOs\Admin\Inventory\Stock\KardexFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GetKardexAction
{
    public function execute(KardexFilterDTO $filters, ?string $adminBranchId = null): LengthAwarePaginator
    {
        $branchId = $adminBranchId ?? $filters->branch_id;

        // 1. CÁLCULO DEL SALDO INICIAL (Opening Balance)
        // Sumamos todo lo que ocurrió ANTES de la fecha de inicio del filtro.
        $openingBalance = 0;
        if ($filters->start_date && $branchId) {
            $openingBalance = InventoryMovement::where('sku_id', $filters->sku_id)
                ->where('branch_id', $branchId)
                ->where('created_at', '<', $filters->start_date)
                ->sum('quantity');
        }

        // 2. QUERY PRINCIPAL CON WINDOW FUNCTION CORREGIDA
        $query = InventoryMovement::query()
            ->select([
                'inventory_movements.*',
                // Inyectamos el openingBalance como base del acumulado
                DB::raw("($openingBalance + SUM(quantity) OVER (
                    PARTITION BY branch_id, sku_id 
                    ORDER BY created_at ASC, id ASC
                )) as running_balance")
            ])
            ->with([
                'admin:id,first_name,last_name',
                'branch:id,name',
                'lot:id,lot_code,expiration_date'
            ])
            ->where('sku_id', $filters->sku_id);

        // Aislamiento de Silo
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Aplicamos filtros de fecha al conjunto de resultados, 
        // pero la Window Function ya procesó el histórico gracias a que Laravel 
        // envuelve esto de forma eficiente si se usa correctamente.
        if ($filters->start_date) {
            $query->where('created_at', '>=', $filters->start_date);
        }
        if ($filters->end_date) {
            $query->where('created_at', '<=', $filters->end_date);
        }

        // Retornamos ordenado por fecha descendente para la UI (lo más reciente arriba)
        return $query->orderBy('created_at', 'desc')->paginate($filters->per_page);
    }
}