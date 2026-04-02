<?php

namespace App\Http\Resources\Customer\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Finance\PriceResolverService;

class FavoriteSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Resolvemos el precio usando el Service que me proporcionaste
        $priceResolver = app(PriceResolverService::class);
        $pricing = $priceResolver->resolveWinningPrice($this->resource, 1, now());

        return [
            'id'          => $this->id,
            'product_id'  => $this->product_id,
            'name'        => $this->name,
            'brand_name'  => $this->product->brand->name ?? 'CyberMarket',
            'image'       => $this->image_path 
                ? asset('storage/' . $this->image_path) 
                : asset('assets/img/placeholders/sku-default.png'),
            
            'final_price' => $pricing->final_price,
            'list_price'  => $pricing->list_price,
            'discount_percentage' => $pricing->list_price > $pricing->final_price 
                ? round((($pricing->list_price - $pricing->final_price) / $pricing->list_price) * 100) 
                : 0,
            
            'stock'       => 10, // Placeholder hasta integrar inventario
            'bg_color'    => $this->bg_color ?? '#4ade80',
            
            'upsell'      => $pricing->next_tier ? [
                'next_price' => $pricing->next_tier['final_price'],
                'next_qty'   => $pricing->next_tier['min_quantity'],
            ] : null,
        ];
    }
}