<?php

namespace App\Actions\Customer\Cart;

use App\Models\Bundle;
use App\DTOs\Customer\Cart\AddBundleDTO;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;

class AddBundleToCartAction
{
    public function __construct(
        protected CartService $cartService
    ) {}


    public function execute(AddBundleDTO $dto): void
    {
        $this->cartService->addBundle(
            bundleId: $dto->bundleId, 
            requestedQty: $dto->quantity, 
            customItems: $dto->customItems,
            guestUuid: $dto->guestUuid // <--- CRÍTICO: Pasar la identidad del invitado
        );
    }
}