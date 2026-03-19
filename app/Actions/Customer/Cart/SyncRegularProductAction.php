<?php

namespace App\Actions\Customer\Cart;

use App\Models\{Cart, CartItem, InventoryLot};

class SyncRegularProductAction
{
    public function execute(CartItem $guestItem, Cart $customerCart): void
    {
        $available = InventoryLot::where('sku_id', $guestItem->sku_id)
            ->where('branch_id', $customerCart->branch_id)
            ->where('is_safety_stock', false)
            ->sum(\DB::raw('quantity - reserved_quantity'));
    
        if ($available <= 0) return;
    
        $finalQuantity = min($guestItem->quantity, $available);
    
        // Buscamos si ya existe el item en el carrito del cliente
        $existing = CartItem::where('cart_id', $customerCart->id)
            ->where('sku_id', $guestItem->sku_id)
            ->whereNull('bundle_id')
            ->first();
    
        $newQty = ($existing ? $existing->quantity : 0) + $finalQuantity;
    
        CartItem::updateOrCreate(
            ['cart_id' => $customerCart->id, 'sku_id' => $guestItem->sku_id, 'bundle_id' => null],
            [
                'quantity'          => $newQty, // Cantidad real procesada en PHP
                'price_at_addition' => $guestItem->price_at_addition,
                'is_bundle'         => false
            ]
        );
    }
}