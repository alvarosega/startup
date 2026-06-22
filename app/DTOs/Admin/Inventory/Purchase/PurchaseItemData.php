<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Purchase;

readonly class PurchaseItemData
{
    public function __construct(
        public string $sku_id,
        public float $quantity
    ) {}
}