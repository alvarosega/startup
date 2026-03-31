<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'sort_order' => $this->sort_order,
        ];
    }
}