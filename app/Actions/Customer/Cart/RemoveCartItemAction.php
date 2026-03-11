<?php

namespace App\Actions\Customer\Cart;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RemoveCartItemAction
{
    public function execute(string $itemId): void
    {
        $item = CartItem::with('cart')->findOrFail($itemId);

        // REGLA ZERO-TRUST: Verificar que el ítem pertenezca al actor autenticado
        $customerId = Auth::guard('customer')->id();
        
        if ($item->cart->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'cart' => 'Acceso denegado: El ítem no pertenece a tu sesión actual.'
            ]);
        }

        $item->delete();
    }
}