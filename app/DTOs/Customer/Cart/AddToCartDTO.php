<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class AddToCartDTO
{
    public function __construct(
        public string $skuId,
        public int $quantity,
        public string $branchId,
        public ?string $customerId = null,
        public ?string $sessionUuid = null
    ) {}

    public static function fromRequest(Request $request, string $activeBranchId): self
    {
        $customerId = Auth::guard('customer')->id();
        
        // Zero-Trust: Si hay cliente autenticado, ignoramos por completo el UUID del localstorage
        $sessionUuid = $customerId ? null : (string) $request->input('guest_client_uuid');

        return new self(
            skuId: (string) $request->input('sku_id'),
            quantity: (int) $request->input('quantity', 1),
            branchId: $activeBranchId,
            customerId: $customerId,
            sessionUuid: $sessionUuid ?: null // Forzamos null si llega vacío
        );
    }
}