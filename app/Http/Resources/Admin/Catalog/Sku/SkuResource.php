<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Catalog\Sku;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string) $this->id,
            'product_id'        => (string) $this->product_id,
            'name'              => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'code'              => (string) $this->code,
            'image_url'         => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'base_price'        => (float) $this->base_price,
            'weight'            => (float) $this->weight,
            'conversion_factor' => (float) $this->conversion_factor,
            'is_active'         => (bool) $this->is_active,
            'sort_order'        => (int) $this->sort_order,
        ];
    }
}