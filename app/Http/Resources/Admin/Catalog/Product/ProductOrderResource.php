<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Catalog\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,
            'name'       => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'sort_order' => (int) $this->sort_order,
            'image_url'  => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
        ];
    }
}