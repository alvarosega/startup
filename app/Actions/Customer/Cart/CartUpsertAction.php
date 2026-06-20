<?php

declare(strict_types=1);

namespace App\Actions\Customer\Cart;

use App\DTOs\Customer\Cart\UpsertCartItemData;
use App\Services\Cart\CartService;
use Illuminate\Validation\ValidationException;

class CartUpsertAction
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Procesa la agregación controlada. Lanza excepción de validación ante quiebres de regla de negocio.
     */
    public function execute(UpsertCartItemData $data, ?string $guestUuid): void
    {
        // En base a la definición de diseño de SKU únicos para ofertas, targetId opera directamente como skuId
        $result = $this->cartService->addSku(
            skuId: $data->targetId,
            quantity: $data->quantity,
            guestUuid: $guestUuid,
            isAbsolute: false // Mutación Relativa (Suma)
        );

        if (!$result->success) {
            throw ValidationException::withMessages([
                'cart' => [$result->message]
            ]);
        }
    }
}