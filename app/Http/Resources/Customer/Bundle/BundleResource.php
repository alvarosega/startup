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
            
            // Los items ya vienen hidratados por el Controller/Action
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'sku_id'          => (string) $sku->id,
                    'name'            => trim(($sku->product?->name ?? '') . ' ' . $sku->name),
                    'image'           => $sku->image_url, // Usar Accessor rectificado
                    'quantity'        => (int)($sku->pivot->quantity ?? 1),
                    'stock_available' => (int)($sku->max_stock ?? 0), // Ya inyectado
                    'final_price'     => (float)($sku->resolved_price->final_price ?? 0), // Ya inyectado
                ]);
            }),
        ];
    }
}