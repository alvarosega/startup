<?php
namespace App\DTOs\Admin\Order;

readonly class ReviewPaymentDTO
{
    public function __construct(
        public string $orderId,
        public string $type,
        public ?string $bankReference = null,
        public ?string $rejectionReason = null
    ) {}
}