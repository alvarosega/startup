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
        
        // Asignación estricta de extensiones correctas
        $placeholder = $isEditable ? 'bundle_banner_editable.webp' : 'bundle_banner_noeditable.webp';

        $version = $this->updated_at?->timestamp ?? time();

        return [
            'id'          => (string) $this->id,
            'name'        => (string) $this->name,
            'slug'        => (string) $this->slug,
            'type'        => (string) $this->type, 
            'is_editable' => (bool) $isEditable,
            'description' => (string) $this->description,
            'fixed_price' => !is_null($this->fixed_price) ? (float)$this->fixed_price : null,
            
            // Si no hay imagen en BD, se pasa la ruta relativa al directorio public/assets/img
            'image_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) . "?v={$version}"
                : "/assets/img/{$placeholder}",
            
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(function($sku) {
                    $stockAvailable = $sku->inventoryLots->sum(fn($lot) => max(0, $lot->quantity - $lot->reserved_quantity));

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
                        'id'              => (string) $sku->id,
                        'sku_id'          => (string) $sku->id,
                        'name'            => trim(($sku->product?->name ?? '') . ' ' . $sku->name),
                        'image'           => $sku->image_path 
                            ? Storage::disk('public')->url($sku->image_path) 
                            : '/assets/img/sku_placeholder.png',
                        'quantity'        => (int)($sku->pivot->quantity ?? 1),
                        'stock_available' => (int)$stockAvailable,
                        'final_price'     => (float)$tiers[0]['final_price'],
                        'price_tiers'     => $tiers,
                    ];
                });
            }),
        ];
    }
}