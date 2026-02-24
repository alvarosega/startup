<?php

namespace App\Actions\Customer\Cart;

use App\Models\{Cart, CartItem, InventoryLot, Price};
use App\DTOs\Customer\Cart\AddToCartDTO;
use Illuminate\Support\Facades\DB;
use Exception;

class AddItemToCartAction
{
    public function execute(AddToCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // 1. Identificar Actor
            $identifier = $dto->customerId 
                ? ['customer_id' => $dto->customerId] 
                : ['session_id' => $dto->guestUuid];
        
            // 2. Cabecera Única
            $cart = Cart::firstOrCreate(
                array_merge($identifier, ['branch_id' => $dto->branchId])
            );

            // 3. Validación de Stock
            $availableStock = InventoryLot::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->sum(DB::raw('quantity - reserved_quantity'));

            if ($availableStock < $dto->quantity) {
                throw new Exception("Stock insuficiente.");
            }

            // 4. Persistencia de Ítem (Método Seguro)
            $item = CartItem::firstOrNew([
                'cart_id' => $cart->id,
                'sku_id'  => $dto->skuId,
            ]);

            // Si es nuevo, quantity será 0 por defecto si lo definiste en migración, 
            // sino, aseguramos el entero.
            $item->quantity = ($item->exists ? $item->quantity : 0) + $dto->quantity;
            $item->save();

            // 5. Sincronización de Sucursal (Si el carrito cambió de zona)
            if ($cart->branch_id !== $dto->branchId) {
                $cart->update(['branch_id' => $dto->branchId]);
            }
        });
    }
}