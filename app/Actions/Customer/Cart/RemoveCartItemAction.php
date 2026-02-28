<?php

namespace App\Actions\Customer\Cart;

use App\Models\CartItem;

class RemoveCartItemAction
{
    public function execute(string $itemId): void
    {
        $item = CartItem::findOrFail($itemId);
        $item->delete();
    }
}