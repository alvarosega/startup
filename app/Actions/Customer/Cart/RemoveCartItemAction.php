<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\CartItem;

class RemoveCartItemAction
{
    /**
     * Remoción atómica de una línea del carrito.
     */
    public function execute(string $itemId): void
    {
        $item = CartItem::findOrFail($itemId);
        $item->delete();
    }
}