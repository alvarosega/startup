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
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'type'        => $this->type, 
            'description' => $this->description,
            'fixed_price' => $this->fixed_price ? (float)$this->fixed_price : null,
            'image_url'   => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(function($sku) {
                    
                    // 1. Stock Neto (Disponible - Reservado)
                    $stockAvailable = $sku->inventoryLots->sum(fn($lot) => max(0, $lot->quantity - $lot->reserved_quantity));

                    // 2. Resolución de Tiers por Prioridad
                    $now = now();
                    $tiers = $sku->prices
                        ->filter(fn($p) => (is_null($p->valid_from) || $p->valid_from <= $now) && (is_null($p->valid_to) || $p->valid_to >= $now))
                        ->sortByDesc('priority')
                        ->groupBy('min_quantity')
                        ->map(fn($group) => $group->first())
                        ->values()
                        ->map(fn($p) => [
                            'min_quantity' => (int)$p->min_quantity,
                            'final_price'  => (float)$p->final_price,
                        ])->toArray();

                    if (empty($tiers)) {
                        $tiers = [['min_quantity' => 1, 'final_price' => (float)($sku->base_price ?? 0)]];
                    }

                    return [
                        'id'              => $sku->id,
                        'sku_id'          => $sku->id, // Compatibilidad con selectores Vue
                        'name'            => trim(($sku->product?->name ?? '') . ' ' . $sku->name),
                        'image'           => $sku->image_path ? Storage::disk('public')->url($sku->image_path) : null,
                        'quantity'        => (int)($sku->pivot->quantity ?? 1), // Cantidad sugerida/fija
                        'stock_available' => (int)$stockAvailable,
                        'final_price'     => (float)$tiers[0]['final_price'],
                        'price_tiers'     => $tiers,
                    ];
                });
            }),
        ];
    }
}