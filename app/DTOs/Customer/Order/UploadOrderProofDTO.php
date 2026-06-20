<?php

namespace App\DTOs\Customer\Order;

use App\Http\Requests\Customer\Order\UploadOrderProofRequest;
use Illuminate\Http\UploadedFile;

readonly class UploadOrderProofDTO
{
    public function __construct(
        public string $customerId,
        public string $orderId,
        public UploadedFile $proofFile
    ) {}

    public static function fromRequest(UploadOrderProofRequest $request, string $orderId, string $customerId): self
    {
        return new self(
            customerId: $customerId,
            orderId: $orderId,
            proofFile: $request->file('proof')
        );
    }
}