<?php

namespace App\Http\Resources\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'branch_id'   => $this->branch_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'fixed_price' => $this->fixed_price,
            'is_active'   => $this->is_active,
            'image_url'   => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'items'       => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'sku_id'   => $sku->id,
                    'name'     => $sku->name,
                    'quantity' => $sku->pivot->quantity,
                    'price'    => $sku->base_price
                ]);
            }),
        ];
    }
}