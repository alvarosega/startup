<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ShopContextService;

class ShopProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Accedemos al modelo subyacente de forma segura
        $sku = $this->resource;
        $branchId = app(ShopContextService::class)->getActiveBranchId();
        $now = now();

        // 1. Filtrado de precios en memoria (Pilar 3.A: No N+1)
        $prices = collect($sku->prices ?? []);
        
        $validPrices = $prices->where('branch_id', $branchId)
            ->filter(function($p) use ($now) {
                $fromOk = is_null($p->valid_from) || $p->valid_from <= $now;
                $toOk   = is_null($p->valid_to) || $p->valid_to >= $now;
                return $fromOk && $toOk;
            });

        // 2. Precio Ganador (Qty 1)
        $winningPrice = $validPrices
            ->where('min_quantity', '<=', 1)
            ->sortByDesc('priority')
            ->sortByDesc('min_quantity')
            ->first();

        $finalPrice = $winningPrice ? (float) $winningPrice->final_price : (float) $sku->base_price;
        $listPrice  = $winningPrice ? (float) $winningPrice->list_price : (float) $sku->base_price;

        // 3. Descuento
        $discountPct = ($listPrice > $finalPrice) 
            ? round((($listPrice - $finalPrice) / $listPrice) * 100) 
            : 0;

        // 4. Siguiente Escala (Upsell)
        $nextTier = $validPrices
            ->where('min_quantity', '>', 1)
            ->where('final_price', '<', $finalPrice)
            ->sortBy('min_quantity')
            ->first();

        return [
            'id'         => (string) $sku->id,
            'name'       => (string) mb_convert_encoding($sku->name, 'UTF-8', 'UTF-8'),
            'brand_name' => (string) ($sku->product->brand->name ?? 'Genérico'),
            'image'      => $sku->image_url ?? asset('assets/img/placeholder.png'),
            'stock'      => (int) ($sku->available_stock ?? 0),
            'price'      => [
                'final'     => $finalPrice,
                'list'      => $listPrice,
                'discount'  => $discountPct,
                'next_tier' => $nextTier ? [
                    'min_qty' => $nextTier->min_quantity,
                    'price'   => (float) $nextTier->final_price
                ] : null
            ]
        ];
    }
}