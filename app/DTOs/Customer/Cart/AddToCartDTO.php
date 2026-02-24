<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class AddToCartDTO
{
    public function __construct(
        public string $skuId,
        public int $quantity,
        public string $branchId, // <--- CAMBIO: de int a string
        public ?string $customerId = null,
        public ?string $guestUuid = null
    ) {}

    public static function fromRequest(Request $request, string $activeBranchId): self
    {
        return new self(
            skuId: $request->input('sku_id'),
            quantity: (int) $request->input('quantity', 1),
            branchId: $activeBranchId, // <--- CAMBIO: de int a string
            customerId: Auth::guard('customer')->id(),
            guestUuid: $request->input('guest_client_uuid')
        );
    }
}