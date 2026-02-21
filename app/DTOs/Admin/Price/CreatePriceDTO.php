<?php

namespace App\DTOs\Admin\Price;

use Illuminate\Http\Request;

readonly class CreatePriceDTO
{
    public function __construct(
        public string $skuId,
        public string $branchId,
        public string $type,
        public float $listPrice,
        public float $finalPrice,
        public int $minQuantity,
        public int $priority,
        public string $validFrom,
        public ?string $validTo
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            skuId: $request->validated('sku_id'),
            branchId: $request->validated('branch_id'),
            type: $request->validated('type'),
            listPrice: (float) $request->validated('list_price'),
            finalPrice: (float) $request->validated('final_price'),
            minQuantity: (int) $request->validated('min_quantity', 1),
            priority: (int) $request->validated('priority', 0),
            validFrom: $request->validated('valid_from'),
            validTo: $request->validated('valid_to')
        );
    }
}