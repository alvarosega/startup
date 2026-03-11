<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Finance\PriceResolverService;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sku = $this->sku;
        $quantity = (int) $this->quantity;
        
        $resolver = app(PriceResolverService::class);
        $priceResult = $resolver->resolveWinningPrice($sku, $this->cart->branch_id, $quantity);

        $unitFinalPrice = (float) $priceResult->final_price;
        $unitListPrice = (float) $priceResult->list_price;
        
        $subtotal = (float) ($quantity * $unitFinalPrice);
        $totalSavings = (float) (($unitListPrice - $unitFinalPrice) * $quantity);

        // Cálculo directo del stock (relación inventoryLots filtrada por Action)
        $availableStock = (int) $sku->inventoryLots->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);

        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $sku->id,
            'name'         => $this->sanitize($sku->product->name . " " . $sku->name),
            'image'        => (string) ($sku->image_url ?? $sku->image_path),
            
            // Mantenemos estas variables en la raíz para no romper tu actual Index.vue
            'unit_price'   => $unitFinalPrice,
            'list_price'   => $unitListPrice,
            'quantity'     => $quantity,
            'subtotal'     => $subtotal,
            'line_savings' => $totalSavings,
            'max_stock'    => max(0, $availableStock),
            'has_stock'    => $availableStock >= $quantity,
            
            'price_label'  => strtoupper($priceResult->type),
            
            'upsell' => $priceResult->next_tier ? [
                'needed_quantity' => $priceResult->next_tier->min_quantity - $quantity,
                'potential_price' => (float) $priceResult->next_tier->final_price,
                'message'         => "¡Agrega " . ($priceResult->next_tier->min_quantity - $quantity) . " más para precio mayorista!"
            ] : null,
        ];
    }

    private function sanitize(string $str): string {
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}