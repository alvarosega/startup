<?php
namespace App\Actions\Admin\Price;

use App\Models\Price;
use App\DTOs\Admin\Price\CreatePriceDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StorePriceAction
{
    public function execute(CreatePriceDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // 1. Buscamos si existe una regla activa del mismo tipo
            $existing = Price::where('sku_id', $dto->skuId)
                ->where('branch_id', $dto->branchId)
                ->where('type', $dto->type)
                ->whereNull('deleted_at')
                ->first();

            // 2. Si existe, lo anulamos (SoftDelete) para mantener el historial
            if ($existing) {
                $existing->update(['updated_by_id' => Auth::id()]);
                $existing->delete();
            }

            // 3. Creamos el nuevo registro con la autoría
            Price::create([
                'sku_id'         => $dto->skuId,
                'branch_id'      => $dto->branchId,
                'type'           => $dto->type,
                'final_price'    => $dto->finalPrice,
                'list_price'     => $dto->listPrice ?? $dto->finalPrice,
                'min_quantity'   => $dto->minQuantity,
                'priority'       => $dto->priority,
                'valid_from'     => $dto->validFrom,
                'valid_to'       => $dto->validTo,
                'created_by_id'  => Auth::id(),
                'updated_by_id'  => Auth::id(),
            ]);
        });
    }
}