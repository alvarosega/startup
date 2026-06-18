<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Modifica de forma absoluta la cantidad de un ítem existente recalculando su escala de precio.
     */
    public function execute(string $itemId, int $quantity, ?string $guestUuid): void
    {
        $item = CartItem::findOrFail($itemId);

        $result = $this->cartService->addSku(
            skuId: $item->sku_id,
            quantity: $quantity,
            guestUuid: $guestUuid,
            isAbsolute: true // Mutación Absoluta (Reemplazo forzado)
        );

        if (!$result->success) {
            throw ValidationException::withMessages([
                'cart' => [$result->message]
            ]);
        }
    }
}