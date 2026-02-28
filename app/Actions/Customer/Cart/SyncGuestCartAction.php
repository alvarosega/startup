<?php

namespace App\Actions\Customer\Cart;

use App\Models\{Cart, CartItem, InventoryLot};
use App\DTOs\Customer\Cart\SyncCartDTO;
use Illuminate\Support\Facades\DB;

class SyncGuestCartAction
{
    public function execute(SyncCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $guestCart = Cart::where('session_id', $dto->guestUuid)
                ->where('branch_id', $dto->branchId)
                ->whereNull('customer_id')
                ->with('items')
                ->first();
        
            if (!$guestCart) return;

            $customerCart = Cart::firstOrCreate(
                ['customer_id' => $dto->customerId, 'branch_id' => $dto->branchId]
            );

            foreach ($guestCart->items as $item) {
                // Cálculo de stock real estricto
                $stockAvailable = InventoryLot::where('branch_id', $dto->branchId)
                    ->where('sku_id', $item->sku_id)
                    ->where('is_safety_stock', false) // <--- FILTRO APLICADO
                    ->sum(DB::raw('quantity - reserved_quantity'));

                if ($stockAvailable <= 0) continue;

                $finalQuantity = min($item->quantity, $stockAvailable);

                $existingItem = CartItem::where('cart_id', $customerCart->id)
                    ->where('sku_id', $item->sku_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->increment('quantity', $finalQuantity);
                    $item->delete();
                } else {
                    $item->update([
                        'cart_id' => $customerCart->id,
                        'quantity' => $finalQuantity
                    ]);
                }
            }

            $guestCart->forceDelete();
        });
    }
}