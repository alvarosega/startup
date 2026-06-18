<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Inventory\InventoryLookupService;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $skuLoaded = $this->relationLoaded('sku');
        $sku = $skuLoaded ? $this->sku : null;
        $priceData = $sku ? $sku->resolved_price : null;

        // Resolución de precio de lista y cálculo de ahorro unitario
        $priceAtAddition = (float) $this->price_at_addition;
        $listPrice = $priceData ? (float) ($priceData->list_price ?? $priceAtAddition) : $priceAtAddition;
        $unitSaving = max(0.00, $listPrice - $priceAtAddition);

        // Consulta en caliente del stock disponible real en la sucursal del carrito
        $inventoryService = app(InventoryLookupService::class);
        $maxStock = $sku ? $inventoryService->getAvailableStock($this->sku_id, $this->cart->branch_id) : 0;

        $upsell = null;
        if ($priceData && isset($priceData->next_tier)) {
            $nextTier = $priceData->next_tier;
            $upsell = [
                'next_qty'   => (int) ($nextTier['min_quantity'] ?? 0),
                'next_price' => (float) ($nextTier['final_price'] ?? 0),
                'needed'     => (int) max(0, (int)$nextTier['min_quantity'] - $this->quantity),
            ];
        }

        return [
            'id'                => $this->id,
            'sku_id'            => $this->sku_id,
            'quantity'          => $this->quantity,
            'unit_price'        => $priceAtAddition,
            'list_price'        => $listPrice,
            'subtotal'          => (float) ($this->quantity * $priceAtAddition),
            'line_savings'      => (float) ($this->quantity * $unitSaving), // Mapeo exacto para Vue
            'max_stock'         => (int) $maxStock,                         // Mapeo exacto para Vue
            'upsell'            => $upsell,
            'name'              => $sku ? (string) $sku->name : 'Producto no disponible',
            'code'              => $sku ? (string) $sku->code : '',
            'brand_name'        => $sku ? (string) ($sku->brand_name ?? $sku->product?->brand?->name ?? 'DIGITAL UNIT') : 'DIGITAL UNIT',
            'image'             => $sku && $sku->image_path 
                                    ? asset('storage/' . $sku->image_path) 
                                    : asset('assets/img/sku_placeholder.png'), // Mapeo exacto para Vue
        ];
    }
}