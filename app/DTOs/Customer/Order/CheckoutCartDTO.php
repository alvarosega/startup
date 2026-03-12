<?php

namespace App\DTOs\Customer\Order;

use Illuminate\Http\Request;

class CheckoutCartDTO
{
    public function __construct(
        public readonly string $customerId,
        public readonly string $branchId,
        public readonly string $deliveryType,
        public readonly ?string $addressId,
        public readonly string $paymentMethod = 'qr',
    ) {}

    /**
     * Factory method para crear el DTO desde el FormRequest validado.
     */
    public static function fromRequest(Request $request, string $customerId, string $branchId): self
    {
        return new self(
            customerId: $customerId,
            branchId:   $branchId,
            deliveryType: $request->validated('delivery_type'),
            addressId:    $request->validated('address_id'), // Puede ser null si es pickup
            paymentMethod: $request->validated('payment_method', 'qr')
        );
    }
}