<?php
namespace App\DTOs\Purchase;

class PurchaseItemDTO
{
    public function __construct(
        public readonly string $sku_id,
        public readonly int $quantity,
        public readonly float $unit_cost,
        public readonly float $total_cost,
        public readonly ?string $expiration_date,
    ) {}
}