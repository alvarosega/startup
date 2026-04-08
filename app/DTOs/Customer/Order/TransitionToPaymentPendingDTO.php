<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Orders;

use Illuminate\Http\UploadedFile;

readonly class TransitionToPaymentPendingDTO
{
    public function __construct(
        public string $orderId,
        public string $customerId,
        public UploadedFile $proofFile
    ) {}
}