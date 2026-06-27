<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

readonly class PriceDataDTO
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

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            skuId: (string) $validated['sku_id'],
            branchId: (string) $validated['branch_id'],
            type: (string) $validated['type'],
            listPrice: (float) $validated['list_price'],
            finalPrice: (float) $validated['final_price'],
            minQuantity: (int) $validated['min_quantity'],
            priority: (int) $validated['priority'],
            validFrom: (string) $validated['valid_from'],
            validTo: isset($validated['valid_to']) ? (string) $validated['valid_to'] : null
        );
    }
}