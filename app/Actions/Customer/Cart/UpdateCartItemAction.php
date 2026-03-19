<?php

namespace App\Actions\Customer\Cart;

use App\Models\{CartItem, InventoryLot};
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;
use Exception;

class UpdateCartItemAction
{
    public function __construct(protected CartService $cartService) {}

    public function execute(string $cartItemId, int $newQuantity, string $branchId): void
    {
        // Cargamos la relación mínima necesaria
        $item = CartItem::with(['bundle.skus'])->findOrFail($cartItemId);

        if ($item->is_bundle && $item->bundle) {
            // 1. Caso Bundle: El Service ya tiene esta lógica pública y funcional
            $this->cartService->validateBundleStock($item->bundle, $newQuantity, $branchId);
        } else {
            // 2. Caso SKU: Realizamos la validación directa aquí para no tocar el Service
            $available = (int) InventoryLot::where('sku_id', $item->sku_id)
                ->where('branch_id', $branchId)
                ->where('is_safety_stock', false)
                ->selectRaw('SUM(quantity - reserved_quantity) as total')
                ->value('total') ?? 0;

            if ($available < $newQuantity) {
                throw new Exception("Stock insuficiente. Solo quedan {$available} unidades.");
            }
        }

        // 3. Actualización de cantidad
        $item->update(['quantity' => $newQuantity]);
    }
}