<?php

namespace App\Actions\Customer\Cart;

use App\Models\{CartItem, InventoryLot};
use Illuminate\Support\Facades\DB;
use Exception;

class UpdateCartItemAction
{
    public function execute(string $itemId, int $quantity, string $branchId): void
    {
        $item = CartItem::findOrFail($itemId);

        $availableStock = InventoryLot::where('branch_id', $branchId)
            ->where('sku_id', $item->sku_id)
            ->where('is_safety_stock', false) // <--- CORTE QUIRÚRGICO AQUÍ
            ->sum(DB::raw('quantity - reserved_quantity'));

        if ($quantity > $availableStock) {
            throw new Exception("Stock insuficiente.");
        }

        $item->update(['quantity' => $quantity]);
    }
}