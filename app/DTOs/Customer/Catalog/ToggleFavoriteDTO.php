<?php

namespace App\DTOs\Customer\Catalog;

use Illuminate\Support\Facades\Auth;

readonly class ToggleFavoriteDTO
{
    public function __construct(
        public string $productId,
        public string $customerId
    ) {}

    public static function fromRequest(string $productId): self
    {
        return new self(
            productId: $productId,
            customerId: (string) Auth::guard('customer')->id()
        );
    }
}