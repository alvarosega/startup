<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Order\States;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'delivery_type' => $this->delivery_type,
            'total_amount' => (float) $this->total_amount,
            
            // Relaciones para vistas detalladas de Pickup
            'branch' => $this->whenLoaded('branch', fn () => [
                'name' => $this->branch->name,
                'address' => $this->branch->address,
                'coordinates' => $this->branch->polygon
            ]),
            
            // Relaciones para listado mínimo (si aplica en la vista)
            'items' => $this->whenLoaded('items', fn () => 
                $this->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                ])
            ),
        ];
    }
}