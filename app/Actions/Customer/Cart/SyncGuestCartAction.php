<?php

namespace App\Actions\Customer\Cart;

use App\Models\{Cart, CartItem};
use Illuminate\Support\Facades\DB;

class SyncCartAction
{
    public function execute(string $customerId, string $guestUuid, string $branchId): void
    {
        DB::transaction(function () use ($customerId, $guestUuid, $branchId) {
            $guestCart = Cart::where('session_id', $guestUuid)->where('branch_id', $branchId)->first();
            if (!$guestCart) return;

            $userCart = Cart::firstOrCreate(
                ['customer_id' => $customerId, 'branch_id' => $branchId],
                ['id' => \Illuminate\Support\Str::uuid()]
            );

            foreach ($guestCart->items as $guestItem) {
                $existingItem = CartItem::where('cart_id', $userCart->id)
                    ->where('sku_id', $guestItem->sku_id)
                    ->first();

                if ($existingItem) {
                    // REGLA: Fusión de cantidades (Máximo 99)
                    $existingItem->update([
                        'quantity' => min(99, $existingItem->quantity + $guestItem->quantity)
                    ]);
                    $guestItem->delete();
                } else {
                    // Migración de propiedad
                    $guestItem->update(['cart_id' => $userCart->id]);
                }
            }

            $guestCart->delete(); // Destrucción del contenedor temporal
        });
    }
}