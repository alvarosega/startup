<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Price;

use App\Models\Inventory\Price;
use App\DTOs\Admin\Inventory\Price\MassPriceData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

final class StoreMassPrices
{
    public function execute(MassPriceData $data): void
    {
        DB::transaction(function () use ($data) {
            $adminId = Auth::id();

            foreach ($data->selected_skus as $skuId) {
                // Mitigación de colisiones: Desactivar reglas idénticas previas en el mismo nodo para evitar solapamientos
                DB::table('prices')
                    ->where('branch_id', $data->branch_id)
                    ->where('sku_id', $skuId)
                    ->where('type', $data->type)
                    ->where('min_quantity', $data->min_quantity)
                    ->where('deleted_epoch', 0)
                    ->update([
                        'deleted_epoch' => time(),
                        'deleted_at'    => now(),
                        'updated_at'    => now()
                    ]);

                // Inyección atómica de la nueva celda de precio
                Price::create([
                    'sku_id'        => $skuId,
                    'branch_id'     => $data->branch_id,
                    'type'          => $data->type,
                    'list_price'    => $data->list_price,
                    'final_price'   => $data->final_price,
                    'min_quantity'  => $data->min_quantity,
                    'priority'      => $data->priority,
                    'deleted_epoch' => 0,
                    'valid_from'    => $data->valid_from,
                    'valid_to'      => $data->valid_to,
                    'created_by_id' => $adminId,
                    'updated_by_id' => $adminId,
                ]);
            }
        });
    }
}