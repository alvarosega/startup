<?php

namespace App\Http\Resources\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'branch_id'   => $this->branch_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            
            // Cast numérico para evitar errores de cálculo en Vue
            'fixed_price' => $this->fixed_price ? (float) $this->fixed_price : null,
            'is_active'   => (bool) $this->is_active,
            'starts_at' => $this->starts_at instanceof \Carbon\Carbon 
                ? $this->starts_at->toIso8601String() 
                : null,

            'ends_at'   => $this->ends_at instanceof \Carbon\Carbon 
                ? $this->ends_at->toIso8601String() 
                : null,
            'image_url'   => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) 
                : asset('assets/img/placeholder.png'),

            // Carga condicional optimizada de relaciones
            'branch_name' => $this->whenLoaded('branch', fn() => $this->branch->name),
            
            'items'       => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'sku_id'   => $sku->id,
                    'name'     => $sku->name,
                    'quantity' => (int) $sku->pivot->quantity,
                    'price'    => (float) $sku->base_price
                ]);
            }),
        ];
    }
}