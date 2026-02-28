<?php

namespace App\DTOs\Customer\Order;

readonly class GetCustomerOrderDTO
{
    public function __construct(
        public string $customerId,
        public ?string $orderId = null
    ) {}
}