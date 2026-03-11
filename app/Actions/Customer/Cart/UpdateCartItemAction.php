<?php

namespace App\Actions\Customer\Cart;

use App\Models\{CartItem, InventoryLot};
use Illuminate\Support\Facades\{DB, Auth};
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction
{
    /**
     * Actualiza la cantidad verificando stock y propiedad del ítem.
     * * @throws ValidationException
     */
    public function execute(string $itemId, int $quantity, string $branchId): void
    {
        // 1. Carga del ítem con su relación de carrito para validar propiedad
        $item = CartItem::with('cart')->findOrFail($itemId);

        // 2. REGLA ZERO-TRUST: Verificar identidad del actor
        $customerId = Auth::guard('customer')->id();
        $guestUuid = request()->header('X-Guest-UUID');

        $isOwner = ($customerId && $item->cart->customer_id === $customerId) || 
                   ($guestUuid && $item->cart->session_id === $guestUuid);

        if (!$isOwner) {
            throw ValidationException::withMessages([
                'quantity' => 'Acceso denegado: No puedes modificar este carrito.'
            ]);
        }

        // 3. CÁLCULO DE STOCK DISPONIBLE (Sin contar stock de seguridad)
        $availableStock = (int) InventoryLot::where('branch_id', $branchId)
            ->where('sku_id', $item->sku_id)
            ->where('is_safety_stock', false)
            ->sum(DB::raw('quantity - reserved_quantity'));

        if ($quantity > $availableStock) {
            throw ValidationException::withMessages([
                'quantity' => "Lo sentimos, solo quedan {$availableStock} unidades disponibles."
            ]);
        }

        // 4. PERSISTENCIA ATÓMICA
        $item->update(['quantity' => $quantity]);
    }
}