<?php

namespace App\Actions\Customer\Cart;

use App\Models\{Cart, CartItem, InventoryLot};
use App\DTOs\Customer\Cart\AddToCartDTO;
use Illuminate\Support\Facades\DB;
use Exception;

class AddItemToCartAction
{
    // --- MODIFICAR EL PASO 1 Y ELIMINAR LA ACTUALIZACIÓN ---
    public function execute(AddToCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $identifier = $dto->customerId 
                ? ['customer_id' => $dto->customerId] 
                : ['session_id' => $dto->guestUuid];

            // 1. El branch_id ahora es un criterio de búsqueda, no solo de creación.
            // Esto crea un carrito único por (Usuario + Sucursal)
            $cart = Cart::firstOrCreate(
                array_merge($identifier, ['branch_id' => $dto->branchId])
            );

            // 2. Obtener ítem y validar stock acumulado (se mantiene igual)
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $dto->skuId)
                ->first();

            $currentInCart = $existingItem ? $existingItem->quantity : 0;
            $totalRequested = $currentInCart + $dto->quantity;

            $availableStock = InventoryLot::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->where('is_safety_stock', false) // <--- CORTE QUIRÚRGICO AQUÍ
                ->sum(DB::raw('quantity - reserved_quantity'));

            if ($availableStock < $totalRequested) {
                throw new Exception("Stock insuficiente en esta sucursal.");
            }

            if ($existingItem) {
                $existingItem->update(['quantity' => $totalRequested]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'sku_id'  => $dto->skuId,
                    'quantity' => $dto->quantity
                ]);
            }
        });
    }
}