<?php

namespace App\DTOs\Customer\Cart;

readonly class SyncCartDTO
{
    public function __construct(
        public string $customerId,
        public string $guestUuid,
        public ?string $branchId = null
    ) {}

    public static function fromRequest(string $customerId, string $guestUuid, string $branchId): self
    {
        return new self($customerId, $guestUuid, $branchId);
    }
}