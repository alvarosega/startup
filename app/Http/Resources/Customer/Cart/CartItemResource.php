<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Finance\PriceResolverService;
use App\Models\InventoryLot;
use Carbon\Carbon;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Lógica pura de mapeo. Sin 'app()', sin 'DB::', sin 'Carbon::'.
        $price = $this->current_price_data; 
    
        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $this->sku_id,
            'name'         => $this->sku->product->name . " " . $this->sku->name,
            'image'        => asset('storage/' . $this->sku->image_path),
            'unit_price'   => (float) $price->final_price,
            'list_price'   => (float) $price->list_price,
            'quantity'     => (int) $this->quantity,
            'max_stock'    => (int) $this->max_stock,
            'subtotal'     => (float) ($this->quantity * $price->final_price),
            'line_savings' => (float) (($price->list_price - $price->final_price) * $this->quantity),
            'price_label'  => strtoupper($price->type),
            'upsell'       => $price->next_tier ? [
                'needed_quantity' => $price->next_tier['min_quantity'] - $this->quantity,
                'potential_price' => (float) $price->next_tier['final_price']
            ] : null,
        ];
    }

    private function resolveAtomicBundle(Carbon $now): array
    {
        $bundle = $this->bundle;

        if (!$bundle) {
            return [
                'id' => (string) $this->id,
                'name' => 'Combo no disponible',
                'is_bundle' => true,
                'has_stock' => false,
                'subtotal' => 0,
                'quantity' => $this->quantity
            ];
        }

        $virtualStock = $this->calculateVirtualStock($bundle);

        return [
            'id'           => (string) $this->id,
            'is_bundle'    => true,
            'bundle_id'    => (string) $bundle->id,
            'name'         => $this->sanitize($bundle->name),
            'image'        => (string) $bundle->image_url,
            'quantity'     => (int) $this->quantity,
            'unit_price'   => (float) $this->price_at_addition,
            'subtotal'     => (float) ($this->quantity * $this->price_at_addition),
            'line_savings' => 0.0,
            'max_stock'    => $virtualStock,
            'has_stock'    => $virtualStock >= $this->quantity,
            'price_label'  => 'COMBO',
            'components'   => $bundle->skus->map(fn($sku) => [
                'name' => $this->sanitize($sku->product->name . " " . $sku->name),
                'qty'  => (int) $sku->pivot->quantity,
            ]),
            'upsell'       => null,
        ];
    }

    private function getSkuData(Carbon $now): array
    {
        $sku = $this->sku;
        $quantity = (int) $this->quantity;
        $resolver = app(PriceResolverService::class);
        
        // 1. RESOLUCIÓN DE PRECIO (Aquí se definen las variables)
        $priceResult = $resolver->resolveWinningPrice($sku, $quantity, $now);
    
        $unitFinalPrice = (float) $priceResult->final_price;
        $unitListPrice = (float) $priceResult->list_price;
    
        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $sku->id,
            'name'         => $this->sanitize(($sku->product->name ?? 'Producto') . " " . $sku->name),
            'image'        => (string) ($sku->image_url ?? $sku->image_path),
            'unit_price'   => $unitFinalPrice, // <--- DEFINIDO
            'list_price'   => $unitListPrice, // <--- DEFINIDO
            'quantity'     => $quantity,
            'subtotal'     => (float) ($quantity * $unitFinalPrice),
            'line_savings' => (float) (($unitListPrice - $unitFinalPrice) * $quantity),
            'price_label'  => strtoupper($priceResult->type),
            // INTEGRIDAD: El upsell ahora es sensible a la cantidad actual en el carrito
            'upsell'       => $priceResult->next_tier ? [
                'next_qty'   => (int) $priceResult->next_tier['min_quantity'],
                'next_price' => (float) $priceResult->next_tier['final_price'],
                'needed'     => (int) ($priceResult->next_tier['min_quantity'] - $quantity),
            ] : null,
        ];
    }

    private function calculateVirtualStock($bundle): int
    {
        if (!$bundle || $bundle->skus->isEmpty()) return 0;

        $availability = [];
        foreach ($bundle->skus as $sku) {
            $stock = InventoryLot::where('sku_id', $sku->id)
                ->where('branch_id', $this->cart->branch_id)
                ->where('is_safety_stock', false)
                ->sum(\DB::raw('quantity - reserved_quantity'));
            
            $availability[] = (int) floor($stock / $sku->pivot->quantity);
        }

        return (int) min($availability);
    }

    private function sanitize(string $str): string {
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}