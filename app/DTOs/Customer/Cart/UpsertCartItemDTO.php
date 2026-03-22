<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;

// Regla 2.A: readonly class obligatoria (PHP 8.2+)
readonly class UpsertCartItemDTO
{
    public function __construct(
        public string $targetId,
        public string $targetType,
        public int $quantity,
        public string $branchId,
        public array $customItems = []
    ) {}

    public static function fromRequest(Request $request, string $branchId): self
    {
        return new self(
            targetId: (string) $request->input('target_id'),
            targetType: (string) $request->input('target_type', 'sku'),
            quantity: (int) $request->input('quantity', 1),
            branchId: $branchId,
            customItems: (array) $request->input('items', [])
        );
    }
}