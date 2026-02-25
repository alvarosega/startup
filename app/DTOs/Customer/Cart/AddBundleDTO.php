<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class AddBundleDTO
{
    public function __construct(
        public string $bundleId,
        public int $quantity, // Cantidad de "combos"
        public string $branchId,
        public ?string $customerId = null,
        public ?string $guestUuid = null
    ) {}

    public static function fromRequest(Request $request, string $activeBranchId): self
    {
        return new self(
            bundleId: $request->input('bundle_id'),
            quantity: (int) $request->input('quantity', 1),
            branchId: $activeBranchId,
            customerId: Auth::guard('customer')->id(),
            guestUuid: $request->input('guest_client_uuid')
        );
    }
}