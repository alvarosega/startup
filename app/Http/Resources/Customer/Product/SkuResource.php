<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imagePath = data_get($this->resource, 'sku_image') 
                  ?? data_get($this->resource, 'product_image') 
                  ?? data_get($this->resource, 'image_path');

        $imageUrl = $imagePath 
            ? asset('storage/' . $imagePath) 
            : asset('assets/img/sku_placeholder.png');

        $finalPrice = (float) (data_get($this->resource, 'final_price') ?? 0);
        $listPrice = (float) (data_get($this->resource, 'list_price') ?? 0);
        
        $discount = ($listPrice > $finalPrice && $listPrice > 0) 
            ? (int) round((($listPrice - $finalPrice) / $listPrice) * 100) 
            : 0;

        return [
            'id'                  => (string) (data_get($this->resource, 'sku_id') ?? data_get($this->resource, 'id')),
            'bg_color' => data_get($this->resource, 'bg_color') 
                  ? '#' . ltrim((string) data_get($this->resource, 'bg_color'), '#') 
                  : null,
            'product_id'          => (string) data_get($this->resource, 'product_id'),
            'name'                => (string) (data_get($this->resource, 'sku_name') ?? data_get($this->resource, 'name')),
            'brand_name'          => (string) data_get($this->resource, 'brand_name'),
            'image'               => $imageUrl,
            'final_price'         => $finalPrice,
            'list_price'          => $listPrice,
            'discount_percentage' => $discount,
            'stock'               => (int) ((data_get($this->resource, 'total_physical') ?? 0) - (data_get($this->resource, 'total_reserved') ?? 0)),
            'is_alcoholic'        => (bool) data_get($this->resource, 'is_alcoholic', false),

            // ============================================================
            // PERSISTENCIA DE CURSOR (VITAL PARA EVITAR EL ERROR 500)
            // Estas llaves deben existir para que el Paginator no muera
            // ============================================================
            'sku_sort'     => data_get($this->resource, 'sku_sort'),
            'product_sort' => data_get($this->resource, 'product_sort'),
            'sku_name'     => data_get($this->resource, 'sku_name') ?? data_get($this->resource, 'name'),
            'final_price'  => $finalPrice, // Requerido si ordenas por precio
        ];
    }
}