<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuResource extends JsonResource
{
    /**
     * Transforma el SKU para el Silo de Customer.
     * Integra resolución de precios dinámicos y lógica de Upsell (Próximo Nivel).
     */
    public function toArray(Request $request): array
    {
        // 1. RESOLUCIÓN DE IMAGEN: SKU > Producto > Placeholder
        $imagePath = data_get($this->resource, 'sku_image') 
                  ?? data_get($this->resource, 'product_image') 
                  ?? data_get($this->resource, 'image_path');

        $imageUrl = $imagePath 
            ? asset('storage/' . $imagePath) 
            : asset('assets/img/sku_placeholder.png');

        // 2. EXTRACCIÓN DE DATOS FINANCIEROS (Desde PriceResolverService)
        // resolved_price es el objeto inyectado en el Action
        $priceData = data_get($this->resource, 'resolved_price');
        
        $finalPrice = (float) ($priceData->final_price ?? data_get($this->resource, 'final_price', 0));
        $listPrice = (float) ($priceData->list_price ?? data_get($this->resource, 'list_price', 0));
        
        $discount = ($listPrice > $finalPrice && $listPrice > 0) 
            ? (int) round((($listPrice - $finalPrice) / $listPrice) * 100) 
            : 0;

        // 3. LÓGICA DE UPSELL (Detección de mejor nivel de precio)
        $upsell = null;
        if (isset($priceData->next_tier) && $priceData->next_tier) {
            $qtyInCart = (int) data_get($this->resource, 'quantity_in_cart', 0);
            $upsell = [
                'next_qty'   => (int) $priceData->next_tier->min_quantity,
                'next_price' => (float) $priceData->next_tier->final_price,
                'needed'     => (int) max(0, $priceData->next_tier->min_quantity - $qtyInCart),
            ];
        }

        return [
            'id'                  => (string) (data_get($this->resource, 'sku_id') ?? data_get($this->resource, 'id')),
            'product_id'          => (string) data_get($this->resource, 'product_id'),
            'name'                => (string) (data_get($this->resource, 'sku_name') ?? data_get($this->resource, 'name')),
            'brand_name'          => (string) data_get($this->resource, 'brand_name'),
            'image'               => $imageUrl,
            
            // Datos de Identidad
            'bg_color' => data_get($this->resource, 'bg_color') 
                ? '#' . ltrim((string) data_get($this->resource, 'bg_color'), '#') 
                : null,

            // Datos Financieros Reactivos
            'final_price'         => $finalPrice,
            'list_price'          => $listPrice,
            'discount_percentage' => $discount,
            'price_type'          => $priceData->type ?? 'regular',
            'upsell'              => $upsell,

            // Estado de Inventario
            'stock'               => (int) ((data_get($this->resource, 'total_physical') ?? 0) - (data_get($this->resource, 'total_reserved') ?? 0)),
            'is_alcoholic'        => (bool) data_get($this->resource, 'is_alcoholic', false),

            // ============================================================
            // PERSISTENCIA DE CURSOR (NO TOCAR: Evita Error 500 en Scroll)
            // ============================================================
            'sku_sort'     => data_get($this->resource, 'sku_sort'),
            'product_sort' => data_get($this->resource, 'product_sort'),
            'sku_name'     => data_get($this->resource, 'sku_name') ?? data_get($this->resource, 'name'),
            'sorting_price' => data_get($this->resource, 'sorting_price') ?? $finalPrice,
        ];
    }
}