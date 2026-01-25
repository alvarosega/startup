<?php

namespace App\DTOs\Price;

class PriceData
{
    public function __construct(
        public readonly string $skuId,
        public readonly ?int $branchId, // Int porque Branch usa ID numérico
        public readonly float $finalPrice,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            skuId: $request->validated('sku_id'),
            branchId: $request->validated('branch_id'),
            finalPrice: (float) $request->validated('final_price'),
        );
    }

    public function toArray(): array
    {
        return [
            'sku_id' => $this->skuId,
            'branch_id' => $this->branchId,
            'final_price' => $this->finalPrice,
        ];
    }
}