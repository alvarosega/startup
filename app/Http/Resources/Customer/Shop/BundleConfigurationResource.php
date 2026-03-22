<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'name'        => (string) mb_convert_encoding($this->name, 'UTF-8', 'UTF-8'),
            'description' => (string) $this->description,
            'image_path'  => (string) $this->image_path,
            'is_editable' => (bool) $this->is_editable,
            // Regla 2.C: Sanitización y mapeo de la relación BelongsToMany
            'items' => $this->skus->map(fn($sku) => [
                'sku_id'  => (string) $sku->id,
                'name'    => (string) $sku->name,
                'image'   => (string) $sku->product->image_path,
                'max_qty' => (int) $sku->pivot->quantity, // Dato de la tabla pivote
                'stock'   => (int) ($sku->stock_qty ?? 0), // Dato inyectado por la Acción
            ]),
        ];
    }
}