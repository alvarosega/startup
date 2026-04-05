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
                    return $this->skus->map(fn($sku) => [
                        // LLAVES ESTÁNDAR (Idénticas a SkuResource)
                        'id'                 => (string) $sku->id,
                        'product_id'         => (string) $sku->product_id,
                        'name'               => trim(($sku->product?->name ?? '') . ' ' . $sku->name),
                        'brand_name'         => $sku->product?->brand?->name ?? 'Digital Unit',
                        'image'              => $sku->image_url, // Usando el Accessor
                        'bg_color'           => $sku->bg_color ? '#' . ltrim((string) $sku->bg_color, '#') : null,
                        'final_price'        => (float) ($sku->resolved_price->final_price ?? 0),
                        'list_price'         => (float) ($sku->resolved_price->list_price ?? 0),
                        'stock'              => (int) ($sku->available_stock ?? 0),
                        'upsell'             => $sku->resolved_price->next_tier ?? null,
                        
                        // LLAVE DE CONTEXTO DE PACK (Exclusiva)
                        'suggested_quantity' => (int) ($sku->pivot->quantity ?? 1),
                    ]);
                }),
        ];
    }
}