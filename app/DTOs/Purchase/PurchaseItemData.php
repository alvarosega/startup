<?php

namespace App\DTOs\Purchase;

class PurchaseItemData
{
    public function __construct(
        public readonly string $skuId,
        public readonly int $quantity,
        public readonly float $unitCost,
        public readonly ?string $expirationDate
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skuId: $data['sku_id'],
            quantity: (int) $data['quantity'],
            unitCost: (float) $data['unit_cost'],
            expirationDate: $data['expiration_date'] ?? null
        );
    }
}