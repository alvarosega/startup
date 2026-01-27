<?php

namespace App\DTOs\Order;

use App\Models\Order;
use App\Http\Requests\Order\UpdateOrderStatusRequest;

class UpdateOrderStatusDTO
{
    public function __construct(
        public readonly Order $order,
        public readonly string $newStatus,
        public readonly ?string $rejectionReason
    ) {}

    public static function fromRequest(UpdateOrderStatusRequest $request, Order $order): self
    {
        return new self(
            order: $order,
            newStatus: $request->validated('status'),
            rejectionReason: $request->validated('rejection_reason')
        );
    }
}