<?php

namespace App\Actions\Price;

use App\DTOs\Price\PriceData;
use App\Models\Sku;

class UpdateBranchPrice
{
    public function execute(PriceData $data): void
    {
        $sku = Sku::findOrFail($data->skuId);
        
        // Usamos el helper que ya definiste en el modelo Sku
        // para mantener la lógica de historial (SoftDelete + Create)
        $sku->updatePrice($data->finalPrice, $data->branchId);
    }
}