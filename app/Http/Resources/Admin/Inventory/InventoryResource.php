<?php

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sku_id'         => $this->sku_id,
            'sku_name'       => $this->sku->name,
            'sku_code'       => $this->sku->code,
            'product_name'   => $this->sku->product->name,
            'brand_name'     => $this->sku->product->brand->name ?? null,
            'branch_name'    => $this->branch->name,
            'total_quantity' => (int) $this->total_quantity,
            'total_reserved' => (int) $this->total_reserved,
            'avg_cost'       => (float) $this->avg_cost,
        ];
    }
}