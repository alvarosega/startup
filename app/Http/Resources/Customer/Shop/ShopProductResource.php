<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Importación obligatoria

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
        $mainVariant = $this->skus->first();
        $priceData = $mainVariant?->prices->first();

        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'brand'     => $this->brand?->name,
            // Aplicación del fallback en el Producto
            'image_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path)
                : asset('assets/img/placeholder.png'),
            'has_stock' => $mainVariant?->skus_count > 0 || $mainVariant?->inventoryLots->sum('quantity') > 0,
            'price'     => $priceData ? (float) $priceData->final_price : 0,
            'variants'  => $this->skus->map(function ($sku) {
                $skuPrice = $sku->prices->first();
                return [
                    'id'        => $sku->id,
                    'name'      => $sku->name,
                    'code'      => $sku->code,
                    'price'     => $skuPrice ? (float) $skuPrice->final_price : 0,
                    // Aplicación del fallback en la Variante (SKU)
                    'image_url' => $sku->image_path 
                        ? Storage::disk('public')->url($sku->image_path)
                        : asset('assets/img/placeholder.png'),
                    'has_stock' => $sku->inventoryLots->sum('quantity') > 0
                ];
            }),
        ];
    }
}