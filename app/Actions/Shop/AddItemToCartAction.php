<?php

namespace App\Actions\Shop;

use App\DTOs\Shop\AddToCartDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Exception;

class AddItemToCartAction
{
    public function execute(AddToCartDTO $dto): void
    {
        // 1. VALIDACIÓN PREVIA DE STOCK (Lectura rápida)
        // Consultamos el stock real disponible (cantidad física - reservas)
        $stockAvailable = InventoryLot::where('branch_id', $dto->branchId)
            ->where('sku_id', $dto->skuId)
            ->sum(DB::raw('quantity - reserved_quantity'));

        if ($stockAvailable < $dto->quantity) {
            throw new Exception("Stock insuficiente en esta sucursal. Disponible: {$stockAvailable}");
        }

        // 2. OPERACIÓN ATÓMICA
        DB::transaction(function () use ($dto, $stockAvailable) {
            
            // A. Obtener o Crear Carrito
            $cart = $this->getOrCreateCart($dto);

            // B. CHECK DE CONFLICTO DE SUCURSAL
            // Si el carrito existente pertenece a otra sucursal (ej: Zona Sur) 
            // y el usuario está comprando en (ej: El Alto), reiniciamos el carrito.
            if ($cart->branch_id !== $dto->branchId) {
                $cart->items()->delete();
                $cart->update(['branch_id' => $dto->branchId]);
            }

            // C. Calcular Nueva Cantidad
            $existingItem = $cart->items()->where('sku_id', $dto->skuId)->first();
            $currentQty = $existingItem ? $existingItem->quantity : 0;
            $finalQty = $currentQty + $dto->quantity;

            // D. Validar Stock Total (Suma de lo que ya tenía + lo nuevo)
            if ($finalQty > $stockAvailable) {
                throw new Exception("No puedes agregar más. Límite de stock alcanzado ({$stockAvailable}).");
            }

            // E. Guardar Item
            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'sku_id' => $dto->skuId],
                ['quantity' => $finalQty]
            );
            
            // F. Actualizar 'updated_at' del carrito para mantenimiento
            $cart->touch();
        });
    }

    private function getOrCreateCart(AddToCartDTO $dto): Cart
    {
        $query = Cart::query();

        if ($dto->user) {
            $query->where('user_id', $dto->user->id);
        } else {
            $query->where('session_id', $dto->sessionId);
        }

        // Buscamos el último carrito activo
        $cart = $query->latest()->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $dto->user?->id,
                'session_id' => $dto->user ? null : $dto->sessionId,
                'branch_id' => $dto->branchId
            ]);
        }

        return $cart;
    }
}