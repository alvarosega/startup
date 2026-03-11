<?php
namespace App\Actions\Admin\Price;

use App\Models\Price;
use App\DTOs\Admin\Price\PriceData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // <--- LA LEY: IMPORTAR CACHE

class UpsertPriceAction
{
    private const PRIORITY_MAP = [
        'regular'     => 1,
        'offer'       => 2,
        'member'      => 3,
        'wholesale'   => 4,
        'liquidation' => 5,
        'staff'       => 6,
    ];

    public function execute(PriceData $dto): void
    {
        DB::transaction(function () use ($dto) {
            $existing = Price::where('sku_id', $dto->skuId)
                ->where('branch_id', $dto->branchId)
                ->where('type', $dto->type)
                ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>', now()))
                ->get();

            foreach ($existing as $price) {
                $price->update(['updated_by_id' => $dto->adminId]);
                $price->delete();
            }

            Price::create([
                'sku_id'        => $dto->skuId,
                'branch_id'     => $dto->branchId,
                'type'          => $dto->type,
                'list_price'    => $dto->listPrice,
                'final_price'   => $dto->finalPrice,
                'min_quantity'  => $dto->minQuantity,
                'priority'      => self::PRIORITY_MAP[$dto->type],
                'valid_from'    => $dto->validFrom,
                'valid_to'      => $dto->validTo,
                'created_by_id' => $dto->adminId,
                'updated_by_id' => $dto->adminId,
            ]);
        });

        // LA LEY: Destruir la caché obsoleta del catálogo/matriz
        Cache::forget('admin_products_list'); 
        // Si en GetPricingMatrixAction usaste un nombre de caché distinto, agrégalo aquí.
    }
}