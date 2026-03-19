<?php

namespace App\Actions\Customer\Cart;

use App\Models\{Cart, CartItem, InventoryLot};
use Exception;

class SyncBundleProductAction
{
    /**
     * Sincroniza bundles no editables (atómicos).
     */
    public function execute(CartItem $guestItem, Cart $customerCart): void
    {
        $bundle = $guestItem->bundle()->with('skus')->first();
        if (!$bundle) return;

        // 1. VALIDACIÓN DE STOCK EN CASCADA
        foreach ($bundle->skus as $sku) {
            // CORRECCIÓN: Usar $sku->pivot->quantity
            $required = $sku->pivot->quantity * $guestItem->quantity;
            
            $available = InventoryLot::where('sku_id', $sku->id)
                ->where('branch_id', $customerCart->branch_id)
                ->where('is_safety_stock', false)
                ->sum(\DB::raw('quantity - reserved_quantity'));

            if ($available < $required) return; // Stock insuficiente, omitimos este bundle
        }

        $existing = CartItem::where('cart_id', $customerCart->id)
            ->where('bundle_id', $bundle->id)
            ->first();

        $newQty = ($existing ? $existing->quantity : 0) + $guestItem->quantity;

        CartItem::updateOrCreate(
            ['cart_id' => $customerCart->id, 'bundle_id' => $bundle->id, 'sku_id' => null],
            [
                'quantity'          => $newQty,
                'price_at_addition' => $guestItem->price_at_addition,
                'is_bundle'         => true
            ]
        );
    }
}