<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory;

readonly class PurchaseItemDataDTO
{
    public function __construct(
        public string $skuId,
        public float $quantity,
        public float $costPrice,
        public ?string $lotCode,
        public ?string $expirationDate
    ) {}
}