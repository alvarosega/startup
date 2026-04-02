<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Finance\PriceResolverService;
use Carbon\Carbon;

class FavoriteSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. RESOLUCIÓN DE PRECIO DINÁMICO
        // Usamos el Service para determinar el precio actual de este SKU (Cantidad 1)
        $priceResolver = app(PriceResolverService::class);
        $pricing = $priceResolver->resolveWinningPrice($this->resource, 1, Carbon::now());

        // 2. CÁLCULO DE DESCUENTO
        $finalPrice = (float) $pricing->final_price;
        $listPrice = (float) $pricing->list_price;
        $discount = ($listPrice > $finalPrice && $listPrice > 0) 
            ? (int) round((($listPrice - $finalPrice) / $listPrice) * 100) 
            : 0;

        // 3. ESTRUCTURA DE UPSELL (Para el SkuCard)
        $upsell = null;
        if ($pricing->next_tier) {
            $upsell = [
                'next_qty'   => (int) $pricing->next_tier['min_quantity'],
                'next_price' => (float) $pricing->next_tier['final_price'],
                'needed'     => (int) $pricing->next_tier['min_quantity'] - 1, // Basado en qty 1
            ];
        }

        return [
            'id'                  => (string) $this->id,
            'product_id'          => (string) $this->product_id,
            'name'                => (string) $this->name,
            'brand_name'          => (string) ($this->product?->brand?->name ?? 'CyberMarket'),
            'image'               => $this->image_path 
                ? asset('storage/' . $this->image_path) 
                : asset('assets/img/sku_placeholder.png'), // Placeholder verificado
            
            'bg_color'            => $this->bg_color ? '#' . ltrim((string) $this->bg_color, '#') : '#4ade80',
            
            'final_price'         => $finalPrice,
            'list_price'          => $listPrice,
            'discount_percentage' => $discount,
            
            'stock'               => 10, // Placeholder hasta integrar inventario real
            'upsell'              => $upsell,
            
            // Metadatos para ordenamiento
            'sort_order'          => $this->sort_order,
            'is_favorite'         => true, // Estamos en la vista de favoritos
        ];
    }
}