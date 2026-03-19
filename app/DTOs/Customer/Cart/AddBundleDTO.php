<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;

class AddBundleDTO
{
    public function __construct(
        public string $bundleId,
        public int $quantity,
        public string $branchId,
        public array $customItems = [], // <--- ESTO ES LO QUE FALTABA
        public ?string $customerId = null,
        public ?string $guestUuid = null
    ) {}

    public static function fromRequest(Request $request, string $branchId): self
    {
        return new self(
            bundleId: $request->input('bundle_id'),
            quantity: (int) $request->input('quantity', 1),
            branchId: $branchId,
            // Asegúrate de que el nombre aquí coincida con el del constructor arriba
            customItems: $request->input('custom_items', []), 
            customerId: auth()->guard('customer')->id(),
            guestUuid: $request->header('X-Guest-UUID') ?? $request->input('guest_client_uuid')
        );
    }
}