<?php

namespace App\DTOs\Customer\Order;

use App\Http\Requests\Customer\Order\CheckoutRequest;

readonly class CheckoutCartDTO
{
    public function __construct(
        public string $branchId,
        public string $customerId,
        public string $deliveryType, 
        public ?string $addressId,
        public string $paymentType // <--- AÑADIDO ESTRICTAMENTE
    ) {}

    public static function fromRequest(CheckoutRequest $request, string $branchId, string $customerId): self
    {
        return new self(
            branchId: $branchId,
            customerId: $customerId,
            deliveryType: $request->validated('delivery_type'), 
            addressId: $request->validated('address_id'),
            paymentType: $request->validated('payment_type') // <--- ASIGNACIÓN
        );
    }
}