<?php

namespace App\Actions\Customer\Cart;

use App\DTOs\Customer\Cart\AddToCartDTO;
use App\Services\Cart\CartService;

class AddItemToCartAction
{
    public function __construct(protected CartService $cartService) {}

    public function execute(AddToCartDTO $dto): void
    {
        // Delegamos al experto
        $this->cartService->addSku(
            skuId:     $dto->skuId,
            quantity:  $dto->quantity,
            guestUuid: $dto->sessionUuid
        );
    }
}