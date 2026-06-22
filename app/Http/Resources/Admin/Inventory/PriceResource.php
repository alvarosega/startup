<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $this->sku_id,
            'branch_id'    => (string) $this->branch_id,
            'branch_name'  => $this->relationLoaded('branch') ? mb_toUpperCase((string) $this->branch->name) : null,
            'type'         => (string) $this->type,
            'list_price'   => (float) $this->list_price,
            'final_price'  => (float) $this->final_price,
            'min_quantity' => (int) $this->min_quantity,
            'priority'     => (int) $this->priority,
            'valid_from'   => $this->valid_from?->format('Y-m-d H:i:s'),
            'valid_to'     => $this->valid_to?->format('Y-m-d H:i:s'),
        ];
    }
}