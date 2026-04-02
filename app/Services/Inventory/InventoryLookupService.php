<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;

class InventoryLookupService
{
    /**
     * LEY DE DISPONIBILIDAD: Stock neto para venta inmediata.
     * Lee del Snapshot (Balances) para evitar colapsar la DB.
     */
    public function getAvailableStock(string $skuId, string $branchId): int
    {
        $balance = DB::table('inventory_balances')
            ->where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->first(['total_physical', 'total_reserved', 'total_safety']);

        if (!$balance) return 0;

        // PROTOCOLO: El stock de seguridad y las reservas se restan del físico.
        return max(0, (int)$balance->total_physical - (int)$balance->total_reserved - (int)$balance->total_safety);
    }
}