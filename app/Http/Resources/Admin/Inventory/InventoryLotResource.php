<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryLotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => (string) $this->id,
            'lot_code'                => (string) $this->lot_code,
            'sku_name'                => $this->relationLoaded('sku') ? mb_strtoupper((string) $this->sku->name) : null,
            'sku_code'                => $this->relationLoaded('sku') ? (string) $this->sku->code : null,
            'quantity'                => (float) $this->quantity,
            'safety_quantity'         => (float) $this->safety_quantity,
            'reserved_quantity'       => (float) $this->reserved_quantity,
            'available_liquid'        => (float) ($this->quantity - $this->reserved_quantity), // Métrica calculada real para venta ordinaria
            'is_quarantine'           => (bool) $this->is_quarantine,
            'expiration_date'         => $this->expiration_date?->format('Y-m-d'),
            'created_at'              => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}