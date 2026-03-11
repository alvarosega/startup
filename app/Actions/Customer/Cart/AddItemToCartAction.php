<?php

namespace App\Actions\Customer\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\InventoryLot;
use App\DTOs\Customer\Cart\AddToCartDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddItemToCartAction
{
    public function execute(AddToCartDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            
            // 1. AISLAMIENTO ZERO-TRUST (Customer vs Guest)
            // Se define de forma explícita qué columnas son nulas para evitar inyecciones cruzadas.
            $cart = Cart::firstOrCreate([
                'branch_id'   => $dto->branchId,
                'customer_id' => $dto->customerId,
                'session_id'  => $dto->sessionUuid,
            ]);

            // 2. CÁLCULO DE STOCK ATÓMICO
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('sku_id', $dto->skuId)
                ->first();

            $currentInCart = $existingItem ? $existingItem->quantity : 0;
            $totalRequested = $currentInCart + $dto->quantity;

            $availableStock = (int) InventoryLot::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->where('is_safety_stock', false)
                ->sum(DB::raw('quantity - reserved_quantity'));

            if ($availableStock < $totalRequested) {
                // Lanzamos ValidationException para que Inertia lo capture automáticamente en $page.props.errors
                throw ValidationException::withMessages([
                    'cart' => 'Stock insuficiente en esta sucursal.'
                ]);
            }

            // 3. UPSERT UNIFICADO (El motor decide si hace INSERT o UPDATE)
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'sku_id'  => $dto->skuId,
                ],
                [
                    'quantity' => $totalRequested,
                ]
            );
            
            // Si en el futuro agregas caché para el contador de carrito, aquí debes purgarlo:
            // Cache::forget("cart_count_{$dto->branchId}_{$dto->customerId}_{$dto->sessionUuid}");
        });
    }
}