<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'type'       => $this->type,
            'is_active'  => $this->is_active,
            'image'      => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'skus_count' => $this->relationLoaded('skus') ? $this->skus->count() : 0,
            'skus'       => $this->whenLoaded('skus', fn() => $this->skus->map(fn($sku) => [
                'id'   => $sku->id,
                'name' => $sku->name,
                'code' => $sku->code
            ])),
        ];
    }
}