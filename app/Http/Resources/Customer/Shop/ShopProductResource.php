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
        
        // USAMOS EL ACCESSOR display_price que ya definimos en el modelo Sku
        // El modelo ya sabe buscar en 'currentPrices' (que filtramos en el Action)
        $finalPrice = $mainVariant ? $mainVariant->display_price : 0;
    
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'brand'     => $this->brand?->name,
            'image_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path)
                : asset('assets/img/placeholder.png'),
            
            // CORRECCIÓN: Usamos el atributo 'available_stock' calculado en el Action
            'has_stock' => $mainVariant && $mainVariant->available_stock > 0,
            'price'     => (float) $finalPrice,
            
            'variants'  => $this->skus->map(function ($sku) {
                return [
                    'id'        => $sku->id,
                    'name'      => $sku->name,
                    'code'      => $sku->code,
                    'price'     => (float) $sku->display_price, // Usar accessor
                    'image_url' => $sku->image_path 
                        ? Storage::disk('public')->url($sku->image_path)
                        : asset('assets/img/placeholder.png'),
                    'has_stock' => $sku->available_stock > 0 // Usar atributo calculado
                ];
            }),
        ];
    }
}