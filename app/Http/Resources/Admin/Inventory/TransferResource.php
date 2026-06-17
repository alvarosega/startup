<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'origin_branch_id' => $this->origin_branch_id,
            'destination_branch_id' => $this->destination_branch_id,
            'status' => $this->status,
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'received_at' => $this->received_at?->toIso8601String(),
            'items' => $this->items->map(fn($item) => [
                'sku_id' => $item->sku_id,
                'qty_sent' => $item->qty_sent,
                'qty_received' => $item->qty_received
            ])
        ];
    }
}