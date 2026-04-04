<?php

namespace App\DTOs\Customer\Order;

use Illuminate\Http\Request;

class CheckoutCartDTO
{
    public function __construct(
        public readonly string $customerId,
        public readonly string $branchId,
        public readonly string $deliveryType,
        public readonly string $paymentMethod = 'qr',
    ) {}

    public static function fromRequest(Request $request, string $customerId, string $branchId): self
    {
        return new self(
            customerId: $customerId,
            branchId:   $branchId,
            deliveryType: $request->validated('delivery_type'),
            paymentMethod: $request->validated('payment_method', 'qr')
        );
    }
}