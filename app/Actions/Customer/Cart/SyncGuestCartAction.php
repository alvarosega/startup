<?php

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\InventoryLot;
use App\DTOs\Customer\Cart\SyncCartDTO;
use Illuminate\Support\Facades\DB;

class SyncGuestCartAction
{
    public function execute(SyncCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // 1. Localizar carritos (Asegura que el nombre sea guestUuid)
            $guestCart = Cart::where('session_id', $dto->guestUuid) 
                ->whereNull('customer_id')
                ->with('items')
                ->first();
        
            if (!$guestCart) return;

            $customerCart = Cart::firstOrCreate(
                ['customer_id' => $dto->customerId],
                ['branch_id' => $dto->branchId]
            );

            // 2. Procesar Items (Merge & Re-validation)
            foreach ($guestCart->items as $item) {
                // Validar Stock Real en la sucursal destino
                $stockAvailable = InventoryLot::where('branch_id', $dto->branchId)
                    ->where('sku_id', $item->sku_id)
                    ->sum(DB::raw('quantity - reserved_quantity'));

                if ($stockAvailable <= 0) {
                    continue; // Omitir si no hay stock en la sucursal del cliente
                }

                $finalQuantity = min($item->quantity, $stockAvailable);

                $existingItem = CartItem::where('cart_id', $customerCart->id)
                    ->where('sku_id', $item->sku_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->increment('quantity', $finalQuantity);
                } else {
                    $item->update([
                        'cart_id' => $customerCart->id,
                        'quantity' => $finalQuantity
                    ]);
                }
            }

            // 3. Limpieza de rastro Zero-Trust
            $guestCart->forceDelete(); 
            
            // 4. Alinear sucursal si hubo cambio
            if ($customerCart->branch_id !== $dto->branchId) {
                $customerCart->update(['branch_id' => $dto->branchId]);
            }
        });
    }
}