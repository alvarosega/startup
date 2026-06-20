<?php

declare(strict_types=1);
namespace App\Http\Resources\Customer\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isEditable = $this->type !== 'atomic';
        $placeholder = $isEditable ? 'bundle_banner_editable.webp' : 'bundle_banner_noeditable.webp';

        return [
            'id'          => (string) $this->id,
            'name'        => (string) $this->name,
            'slug'        => (string) $this->slug,
            'is_editable' => $isEditable,
            'description' => (string) $this->description,
            'fixed_price' => $this->fixed_price ? (float)$this->fixed_price : null,
            'image_url'   => $this->image_path 
                ? asset('storage/' . $this->image_path) 
                : asset('assets/img/' . $placeholder),
            
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(function($sku) {
                    $priceData = $sku->resolved_price;
                    
                    // Formateo de Upsell idéntico a SkuResource
                    $upsell = null;
                    if (isset($priceData->next_tier)) {
                        $nextTier = (array) $priceData->next_tier;
                        $upsell = [
                            'next_qty'   => (int) ($nextTier['min_quantity'] ?? 0),
                            'next_price' => (float) ($nextTier['final_price'] ?? 0),
                        ];
                    }

                return [
                    'id'         => (string) $sku->id,
                    'product_id' => (string) $sku->product_id,
                    'name'       => trim(($sku->product?->name ?? '') . ' ' . $sku->name),
                    'brand_name' => $sku->product?->brand?->name ?? 'Digital Unit',
                    // RECTIFICACIÓN: Ruta correcta de imagen
                    'image'      => $sku->image_path 
                                    ? asset('storage/' . $sku->image_path) 
                                    : asset('assets/img/sku_placeholder.png'), 
                    'bg_color'   => $sku->bg_color ? '#' . ltrim((string) $sku->bg_color, '#') : null,
                    'final_price'=> (float) ($priceData->final_price ?? 0),
                    'list_price' => (float) ($priceData->list_price ?? 0),
                    // RECTIFICACIÓN: El nombre de la propiedad debe ser 'stock' para la SkuCard
                    'stock'      => (int) ($sku->available_stock ?? 0),
                    'upsell'     => $upsell,
                    'suggested_quantity' => (int) ($sku->pivot->quantity ?? 1),
                ];
                });
            }),
        ];
    }
}