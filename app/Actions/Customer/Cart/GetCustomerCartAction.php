<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\Cart;

class GetCustomerCartAction
{
    /**
     * Resuelve de forma aislada la instancia del carrito acoplada al contexto geográfico.
     */
    public function execute(?string $guestUuid, ?string $userId, string $branchId): Cart
    {
        $query = Cart::where('branch_id', $branchId)
            ->with(['items' => function ($q) {
                $q->with('sku')->orderBy('created_at', 'desc');
            }]);

        if ($userId) {
            return $query->where('customer_id', $userId)->first() ?? new Cart();
        }

        if ($guestUuid) {
            return $query->where('session_id', $guestUuid)->first() ?? new Cart();
        }

        return new Cart();
    }
}