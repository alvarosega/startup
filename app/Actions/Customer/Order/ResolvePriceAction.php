<?php

namespace App\Actions\Customer\Order;

use App\Models\Price;
use App\Models\Sku;
use Illuminate\Support\Carbon;

class ResolvePriceAction
{
    public function execute(string $skuId, string $branchId): float
    {
        $now = Carbon::now();

        // 1. Buscar precio específico por sucursal con vigencia activa
        $price = Price::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', $now);
            })
            ->orderBy('priority', 'asc') // Ley: El número menor gana (Ej: 1 supera a 10)
            ->first();

        if ($price) {
            return (float) $price->final_price;
        }

        // 2. Fallback: Precio base global del producto
        $sku = Sku::findOrFail($skuId);
        return (float) $sku->base_price;
    }
}