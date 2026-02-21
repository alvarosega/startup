<?php
namespace App\Actions\Admin\Price;

use App\Models\Price;
use App\DTOs\Admin\Price\PriceDTO;
use Illuminate\Support\Facades\DB;

class SetPriceAction
{
    public function execute(PriceDTO $data): Price
    {
        return DB::transaction(function () use ($data) {
            // 1. Invalidar precios previos del mismo tipo en la misma sucursal
            // si se solapan en tiempo (Regla de negocio para evitar ambigüedad)
            Price::where('sku_id', $data->skuId)
                ->where('branch_id', $data->branchId)
                ->where('type', $data->type)
                ->whereNull('valid_to')
                ->update(['valid_to' => $data->validFrom]);

            // 2. Crear nuevo registro de precio
            return Price::create([
                'sku_id'       => $data->skuId,
                'branch_id'    => $data->branchId,
                'list_price'   => $data->listPrice,
                'final_price'  => $data->finalPrice,
                'type'         => $data->type,
                'min_quantity' => $data->minQuantity,
                'priority'     => $data->priority,
                'valid_from'   => $data->validFrom,
                'valid_to'     => $data->validTo,
            ]);
        });
    }
}