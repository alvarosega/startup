<?php

namespace App\Actions\Admin\Price;

use App\Models\Price;
use App\DTOs\Admin\Price\CreatePriceDTO;
use Illuminate\Support\Facades\DB;

class StorePriceAction
{
    public function execute(CreatePriceDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // Inactivar precios previos del mismo tipo para evitar colisiones
            Price::where('sku_id', $dto->skuId)
                ->where('branch_id', $dto->branchId)
                ->where('type', $dto->type)
                ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>', now()))
                ->update(['valid_to' => now()]);

            Price::create([
                'sku_id'       => $dto->skuId,
                'branch_id'    => $dto->branchId,
                'type'         => $dto->type,
                'list_price'   => $dto->listPrice,
                'final_price'  => $dto->finalPrice,
                'min_quantity' => $dto->minQuantity,
                'priority'     => $dto->priority,
                'valid_from'   => $dto->validFrom,
                'valid_to'     => $dto->validTo
            ]);
        });
    }
}