<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_number' => $this->document_number,
            'purchase_date' => $this->purchase_date->format('Y-m-d'),
            'payment_type' => $this->payment_type,
            'payment_due_date' => $this->payment_due_date ? $this->payment_due_date->format('Y-m-d') : null,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'status' => $this->status,
            
            // Relaciones simples
            'provider' => $this->whenLoaded('provider', fn() => [
                'id' => $this->provider->id,
                'commercial_name' => $this->provider->commercial_name
            ]),
            'branch' => $this->whenLoaded('branch', fn() => [
                'id' => $this->branch->id,
                'name' => $this->branch->name
            ]),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name ?? $this->user->email // Fallback simple
            ]),
            
            // Detalle de Lotes (Items)
            'inventory_lots' => $this->whenLoaded('inventoryLots', function() {
                return $this->inventoryLots->map(function($lot) {
                    return [
                        'id' => $lot->id,
                        'lot_code' => $lot->lot_code,
                        'initial_quantity' => $lot->initial_quantity,
                        'unit_cost' => (float) $lot->unit_cost,
                        'sku' => $lot->sku ? [
                            'name' => $lot->sku->name,
                            'product' => $lot->sku->product ? ['name' => $lot->sku->product->name] : null
                        ] : null
                    ];
                });
            }),
            
            'inventory_lots_count' => $this->whenCounted('inventoryLots'),
        ];
    }
}