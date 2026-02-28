<?php

namespace App\DTOs\Customer\Order;

readonly class CreateOrderDTO
{
    public function __construct(
        public string $customerId,
        public string $branchId,
        public string $deliveryType,
        public array $deliveryData,
        public array $items, // Estructura: [['sku_id' => string, 'quantity' => int], ...]
        public ?string $proofOfPayment = null
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            customerId: auth('customer')->id(),
            branchId: $request->input('branch_id'),
            deliveryType: $request->input('delivery_type'),
            deliveryData: $request->input('delivery_data', []),
            items: $request->input('items'),
            proofOfPayment: $request->input('proof_of_payment')
        );
    }
}