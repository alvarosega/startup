<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'image_url' => $this->image_url, // Usa el accessor del modelo
            'is_active' => (bool) $this->is_active,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            'skus_count' => $this->skus_count ?? 0,
            
            // --- AQUÍ ESTÁ LA SOLUCIÓN ---
            // Usamos un solo bloque para SKUs.
            // Eliminamos cualquier otro 'skus' => ... que tengas más abajo.
            'skus' => $this->skus->map(function ($sku) {
                return [
                    'id' => $sku->id,
                    'name' => $sku->name,
                    'code' => $sku->code,
                    
                    // Precio correcto desde la columna base_price
                    'price' => (float) $sku->base_price, 
                    
                    'conversion_factor' => (float) $sku->conversion_factor,
                    'image_url' => $sku->image_url,
                ];
            }),

            // Relaciones opcionales (Solo si las necesitas explícitamente)
            'brand' => $this->whenLoaded('brand'),
            'category' => $this->whenLoaded('category'),
        ];
    }
}