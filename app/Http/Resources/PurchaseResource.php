<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray($request): array
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
            'inventory_lots_count' => $this->inventory_lots_count ?? 0,
            
            // Relaciones
            'branch' => $this->whenLoaded('branch'),
            'provider' => $this->whenLoaded('provider'),
            'user' => $this->whenLoaded('user', fn() => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                // Si usas Spatie Permissions o un perfil:
                // 'initials' => substr($this->user->name, 0, 2) 
            ]),
            
            // Cargamos lotes solo si es necesario (Detalle expandible)
            'inventory_lots' => $this->whenLoaded('inventoryLots', fn() => $this->inventoryLots->map(fn($lot) => [
                'id' => $lot->id,
                'lot_code' => $lot->lot_code,
                'quantity' => $lot->quantity,
                'initial_quantity' => $lot->initial_quantity,
                'unit_cost' => (float) $lot->unit_cost,
                'sku' => [
                    'name' => $lot->sku->name ?? '?',
                    'product' => [
                        'name' => $lot->sku->product->name ?? '?'
                    ]
                ]
            ])),
        ];
    }
}