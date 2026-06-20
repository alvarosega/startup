<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Price;

use App\Http\Requests\Admin\Price\StorePriceRequest;

readonly class PriceData
{
    public function __construct(
        public string $skuId,
        public string $branchId,
        public string $type,
        public float $listPrice,
        public float $finalPrice,
        public int $minQuantity,
        public string $validFrom,
        public ?string $validTo,
        public string $adminId
    ) {}

    public static function fromRequest(StorePriceRequest $request, string $adminId): self
    {
        return new self(
            skuId: (string) $request->validated('sku_id'),
            branchId: (string) $request->validated('branch_id'),
            type: (string) $request->validated('type'),
            listPrice: (float) $request->validated('list_price'),
            finalPrice: (float) $request->validated('final_price'),
            minQuantity: (int) $request->validated('min_quantity'),
            validFrom: (string) $request->validated('valid_from'),
            validTo: $request->validated('valid_to'),
            adminId: $adminId
        );
    }
}