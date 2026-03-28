<?php

namespace App\Http\Resources\Admin\Inventory\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseLotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'lot_code'          => $this->lot_code,
            'sku_name'          => ($this->sku?->product?->name ?? 'Err') . ' - ' . ($this->sku?->name ?? 'Err'),
            'sku_code'          => $this->sku?->code ?? 'S/N',
            'initial_quantity'  => (int) $this->initial_quantity,
            'current_quantity'  => (int) $this->quantity,
            'reserved_quantity' => (int) $this->reserved_quantity,
            'unit_cost'         => (float) $this->unit_cost,
            'expiration_date'   => $this->expiration_date?->format('Y-m-d'),
        ];
    }
}