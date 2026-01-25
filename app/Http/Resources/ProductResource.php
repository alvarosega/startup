<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'is_active' => (bool) $this->is_active,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            
            'brand_id' => $this->brand_id,
            'brand' => $this->whenLoaded('brand', fn() => ['id' => $this->brand->id, 'name' => $this->brand->name]),
            
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn() => ['id' => $this->category->id, 'name' => $this->category->name]),
            
            // SKUs con precio aplanado para el frontend
            'skus' => $this->whenLoaded('skus', function() {
                return $this->skus->map(function($sku) {
                    // Obtener precio base nacional (el que no tiene branch_id)
                    // Asumimos que 'prices' fue cargado con el filtro correcto en el controlador
                    $priceObj = $sku->prices->first(); 
                    
                    return [
                        'id' => $sku->id,
                        'product_id' => $sku->product_id,
                        'name' => $sku->name,
                        'code' => $sku->code,
                        'weight' => (float) $sku->weight,
                        'conversion_factor' => (float) $sku->conversion_factor,
                        'price' => $priceObj ? (float) $priceObj->final_price : 0,
                    ];
                });
            }),
            
            'skus_count' => $this->whenCounted('skus'),
        ];
    }
}