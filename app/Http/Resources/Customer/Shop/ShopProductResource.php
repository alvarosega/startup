<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductResource extends JsonResource
{
    protected ?string $branchId = null;

    public function setContextBranch(string $branchId): self
    {
        $this->branchId = $branchId;
        return $this;
    }

    public function toArray(Request $request): array
    {
        // Buscamos la variante principal (o la primera activa)
        $mainVariant = $this->skus->first();
        
        // Resolvemos el precio para la sucursal en contexto
        $priceData = $mainVariant?->prices->first();

        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'brand'     => $this->brand?->name,
            'image_url' => $this->image_url, // Asumiendo accessor en modelo Product
            'has_stock' => $mainVariant?->skus_count > 0 || $mainVariant?->inventoryLots->sum('quantity') > 0,
            'price'     => $priceData ? (float) $priceData->final_price : 0,
            'variants'  => $this->skus->map(function ($sku) {
                $skuPrice = $sku->prices->first();
                return [
                    'id'        => $sku->id,
                    'name'      => $sku->name,
                    'code'      => $sku->code,
                    'price'     => $skuPrice ? (float) $skuPrice->final_price : 0,
                    'image_url' => $sku->image_url,
                    'has_stock' => $sku->inventoryLots->sum('quantity') > 0
                ];
            }),
        ];
    }
}