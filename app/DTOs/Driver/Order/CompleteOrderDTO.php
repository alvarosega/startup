<?php

namespace App\DTOs\Driver\Order;

readonly class CompleteOrderDTO
{
    public function __construct(
        public string $orderId,
        public string $driverId,
        public string $otp
    ) {}
}