<?php

namespace App\Http\Resources\Customer\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BundleModalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'fixed_price' => $this->fixed_price ? (float)$this->fixed_price : null,
            'is_editable' => (bool)$this->is_editable,
            'image_url'   => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            
            'skus' => $this->whenLoaded('skus', function() {
                return $this->skus->map(function($sku) {
                    
                    // La data ya está limpia de SQL. 
                    // Si hay varios precios para la misma cantidad (por prioridad), 
                    // groupBy y first() garantizan que Vue reciba solo el ganador.
                    $highestPriorityPrices = $sku->prices
                        ->groupBy('min_quantity')
                        ->map(fn($group) => $group->first())
                        ->values();

                    $tiers = $highestPriorityPrices->map(fn($p) => [
                        'min_quantity' => (int)$p->min_quantity,
                        'final_price'  => (float)$p->final_price,
                    ])->toArray();

                    if (empty($tiers)) {
                        $tiers = [[
                            'min_quantity' => 1,
                            'final_price'  => (float)($sku->base_price ?? 0),
                        ]];
                    }

                    return [
                        'id'          => $sku->id,
                        'name'        => trim(($sku->product?->name ?? '') . ' ' . $sku->name),
                        'base_price'  => (float)($sku->base_price ?? 0),
                        'image'       => $sku->image_path,
                        'default_qty' => (int)($sku->pivot->quantity ?? 1),
                        'price_tiers' => $tiers
                    ];
                });
            })->toArray() // Desempaqueta directo a un array limpio
        ];
    }
}