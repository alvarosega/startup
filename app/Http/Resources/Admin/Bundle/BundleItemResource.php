<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'sku_id'    => (string) $this->sku_id,
            'sku_name'  => $this->relationLoaded('sku') ? mb_toUpperCase((string) $this->sku->name) : null,
            'sku_code'  => $this->relationLoaded('sku') ? (string) $this->sku->code : null,
            'quantity'  => (float) $this->quantity,
        ];
    }
}