<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Finance\PriceResolverService;
use App\Models\InventoryLot;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. Bifurcación limpia: ¿Es un combo atómico o un producto normal?
        if ($this->is_bundle && $this->bundle_id) {
            return $this->resolveAtomicBundle();
        }

        return $this->getSkuData();
    }

    /**
     * Procesa la visualización de un Bundle Atómico (Caja Negra).
     */
    private function resolveAtomicBundle(): array
    {
        $bundle = $this->bundle;

        // PROTECCIÓN: Si el bundle fue eliminado de la DB pero sigue en el carrito
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
            
            // Desglose de componentes para que el usuario sepa qué está comprando
            'components'   => $bundle->skus->map(fn($sku) => [
                'name' => $this->sanitize($sku->product->name . " " . $sku->name),
                'qty'  => (int) $sku->pivot->quantity,
            ]),
            'upsell'       => null,
        ];
    }

    /**
     * Procesa la visualización de un SKU individual.
     */
    private function getSkuData(): array
    {
        $sku = $this->sku;
        $quantity = (int) $this->quantity;
        
        $resolver = app(PriceResolverService::class);
        $priceResult = $resolver->resolveWinningPrice($sku, $this->cart->branch_id, $quantity);

        $unitFinalPrice = (float) $priceResult->final_price;
        $unitListPrice = (float) $priceResult->list_price;
        
        $availableStock = (int) InventoryLot::where('sku_id', $sku->id)
            ->where('branch_id', $this->cart->branch_id)
            ->where('is_safety_stock', false)
            ->sum(\DB::raw('quantity - reserved_quantity'));

        return [
            'id'           => (string) $this->id,
            'is_bundle'    => false,
            'sku_id'       => (string) $sku->id,
            'name'         => $this->sanitize($sku->product->name . " " . $sku->name),
            'image'        => (string) ($sku->image_url ?? $sku->image_path),
            'unit_price'   => $unitFinalPrice,
            'list_price'   => $unitListPrice,
            'quantity'     => $quantity,
            'subtotal'     => $quantity * $unitFinalPrice,
            'line_savings' => (float) (($unitListPrice - $unitFinalPrice) * $quantity),
            'max_stock'    => max(0, $availableStock),
            'has_stock'    => $availableStock >= $quantity,
            'price_label'  => strtoupper($priceResult->type),
            'upsell'       => $priceResult->next_tier ? [
                'needed_quantity' => $priceResult->next_tier->min_quantity - $quantity,
                'potential_price' => (float) $priceResult->next_tier->final_price,
                'message'         => "¡Agrega " . ($priceResult->next_tier->min_quantity - $quantity) . " más para precio mayorista!"
            ] : null,
        ];
    }

    /**
     * Lógica de stock virtual basada en el eslabón más débil.
     */
    private function calculateVirtualStock($bundle): int
    {
        if (!$bundle || $bundle->skus->isEmpty()) return 0;

        $availability = [];
        foreach ($bundle->skus as $sku) {
            $stock = InventoryLot::where('sku_id', $sku->id)
                ->where('branch_id', $this->cart->branch_id)
                ->where('is_safety_stock', false)
                ->sum(\DB::raw('quantity - reserved_quantity'));
            
            // Usamos pivot->quantity porque es la receta del pack
            $availability[] = (int) floor($stock / $sku->pivot->quantity);
        }

        return (int) min($availability);
    }

    private function sanitize(string $str): string {
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}