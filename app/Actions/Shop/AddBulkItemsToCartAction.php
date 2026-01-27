<?php

namespace App\Actions\Shop;

use App\DTOs\Shop\BulkAddToCartDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Exception;

class AddBulkItemsToCartAction
{
    public function execute(BulkAddToCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            
            // 1. Obtener o Crear Carrito
            // Usamos firstOrCreate para asegurar que exista uno para esta sesión/usuario
            $cart = Cart::firstOrCreate(
                $dto->userId ? ['user_id' => $dto->userId] : ['session_id' => $dto->sessionId],
                ['branch_id' => $dto->branchId]
            );

            // Si el carrito existía pero era de otra sucursal, lo actualizamos ahora
            if ($cart->branch_id !== $dto->branchId) {
                $cart->update(['branch_id' => $dto->branchId]);
            }

            // 2. Iterar sobre los items del Bundle
            foreach ($dto->items as $itemData) {
                $skuId = $itemData['sku_id'];
                $qtyToAdd = $itemData['quantity'];

                // A. Verificar Stock Disponible (Validación de Servidor)
                $stockAvailable = InventoryLot::where('branch_id', $dto->branchId)
                    ->where('sku_id', $skuId)
                    ->sum(DB::raw('quantity - reserved_quantity'));

                // B. Buscar item existente para sumar cantidad
                $existingItem = $cart->items()->where('sku_id', $skuId)->first();
                $currentQty = $existingItem ? $existingItem->quantity : 0;
                $finalQty = $currentQty + $qtyToAdd;

                // C. Bloqueo si excede stock
                if ($finalQty > $stockAvailable) {
                    // Nota: El frontend ya debió avisar, pero esto es seguridad final
                    throw new Exception("Stock insuficiente. Máximo disponible: {$stockAvailable}");
                }

                // D. Guardar
                if ($existingItem) {
                    $existingItem->update(['quantity' => $finalQty]);
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'sku_id' => $skuId,
                        'quantity' => $qtyToAdd
                    ]);
                }
            }
            
            // Actualizar timestamp para indicar actividad reciente
            $cart->touch();
        });
    }
}