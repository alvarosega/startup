<?php

namespace App\DTOs\Admin\Inventory;

readonly class PurchaseItemDTO
{
    public function __construct(
        public string $sku_id,
        public int $quantity,
        public float $unit_cost,
        public bool $is_safety_stock = false,
        public ?string $expiration_date = null,
        public ?string $lot_code = null,
    ) {}
}