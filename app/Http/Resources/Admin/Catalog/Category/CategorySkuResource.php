<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Catalog\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategorySkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            'name'         => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'product_name' => $this->product?->name ?? 'SIN PRODUCTO',
            'code'         => (string) $this->code,
            'sort_order'   => (int) $this->sort_order,
            'image'        => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
        ];
    }
}