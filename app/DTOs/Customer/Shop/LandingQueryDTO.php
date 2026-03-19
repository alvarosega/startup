<?php

namespace App\DTOs\Customer\Shop;

readonly class LandingQueryDTO
{
    public function __construct(
        public string $branchId
    ) {}

    public static function fromRequest(string $branchId): self
    {
        return new self($branchId);
    }
}