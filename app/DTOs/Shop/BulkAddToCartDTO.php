<?php

namespace App\DTOs\Shop;

use Illuminate\Http\Request;

class BulkAddToCartDTO
{
    public function __construct(
        public readonly int $branchId,
        public readonly array $items, // Array de ['sku_id', 'quantity']
        public readonly ?string $userId,
        public readonly ?string $sessionId,
    ) {}

    public static function fromRequest(Request $request, int $branchId): self
    {
        return new self(
            branchId: $branchId,
            items: $request->validated('items'),
            userId: $request->user()?->id,
            sessionId: $request->session()->getId()
        );
    }
}