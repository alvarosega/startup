<?php

namespace App\Http\Resources\Admin\Sku;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuSelectResource extends JsonResource
{
    /**
     * Transforma el SKU para su uso en selectores/dropdowns.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id, // String UUID
            'name'  => $this->name,
            // Regla de Rendimiento: Solo cargamos el nombre del producto si la relación existe
            'product_name' => $this->whenLoaded('product', fn() => $this->product->name),
            'base_price'   => (float) $this->base_price,
            'label' => $this->whenLoaded('product', 
                fn() => "{$this->product->name} - {$this->name}", 
                $this->name
            ),
        ];
    }
}