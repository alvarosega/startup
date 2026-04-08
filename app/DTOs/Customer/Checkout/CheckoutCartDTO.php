<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Checkout;

use Illuminate\Http\Request;

readonly class CheckoutCartDTO
{
    public function __construct(
        public string $customerId,
        public string $branchId,
        public string $deliveryType,
        public string $paymentMethod
        // Nota: La IdempotencyKey es consumida por el Middleware, no necesita viajar hasta la acción de base de datos.
    ) {}

    public static function fromRequest(Request $request, string $customerId, string $branchId): self
    {
        return new self(
            customerId: $customerId,
            branchId: $branchId,
            deliveryType: $request->validated('delivery_type'),
            paymentMethod: $request->validated('payment_method')
        );
    }
}