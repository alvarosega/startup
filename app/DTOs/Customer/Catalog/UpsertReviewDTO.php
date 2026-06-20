<?php

namespace App\DTOs\Customer\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class UpsertReviewDTO
{
    public function __construct(
        public string $productId,
        public string $customerId,
        public int $rating,
        public ?string $comment
    ) {}

    public static function fromRequest(Request $request, string $productId): self
    {
        return new self(
            productId: $productId,
            customerId: (string) Auth::guard('customer')->id(),
            rating: (int) $request->input('rating'),
            comment: $request->input('comment')
        );
    }
}