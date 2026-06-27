<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Price;

use App\DTOs\Admin\Inventory\PriceDataDTO;
use App\Models\Inventory\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpsertPriceAction
{
    public function execute(PriceDataDTO $dto, string $adminId): Price
    {
        return DB::transaction(function () use ($dto, $adminId) {
            $activePrice = Price::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->where('type', $dto->type)
                ->where('min_quantity', $dto->minQuantity)
                ->where('priority', $dto->priority)
                ->where('deleted_epoch', 0)
                ->lockForUpdate()
                ->first();

            if ($activePrice) {
                $activePrice->deleted_epoch = time();
                $activePrice->updated_by_id = $adminId;
                $activePrice->saveQuietly();
                $activePrice->delete();
            }

            return Price::create([
                'id' => (string) Str::uuid(),
                'sku_id' => $dto->skuId,
                'branch_id' => $dto->branchId,
                'type' => $dto->type,
                'list_price' => $dto->listPrice,
                'final_price' => $dto->finalPrice,
                'min_quantity' => $dto->minQuantity,
                'priority' => $dto->priority,
                'valid_from' => $dto->validFrom,
                'valid_to' => $dto->validTo,
                'created_by_id' => $adminId,
                'updated_by_id' => null,
                'deleted_epoch' => 0,
            ]);
        });
    }
}