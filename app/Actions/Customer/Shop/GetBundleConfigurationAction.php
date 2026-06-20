<?php

namespace App\Actions\Customer\Shop;

use App\Models\Bundle;
use Illuminate\Support\Facades\DB;

final class GetBundleConfigurationAction
{
    /**
     * Regla 2.B: Acción Atómica de lectura.
     * Retorna el Modelo puro para que el Resource pueda procesarlo.
     */
    public function execute(Bundle $bundle, string $branchId): Bundle
    {
        // 1. Carga de relación BelongsToMany (Sincronizado con tu modelo)
        $bundle->load(['skus.product']);

        // 2. Inyección de stock real en los modelos relacionados
        $bundle->skus->each(function ($sku) use ($branchId) {
            $sku->stock_qty = (int) DB::table('inventory_lots')
                ->where('sku_id', $sku->id)
                ->where('branch_id', $branchId)
                ->where('is_safety_stock', false)
                ->sum(DB::raw('quantity - reserved_quantity'));
        });

        return $bundle;
    }
}