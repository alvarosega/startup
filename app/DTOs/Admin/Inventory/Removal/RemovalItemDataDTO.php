<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Inventory\Removal;

readonly class RemovalItemDataDTO
{
    public function __construct(
        public string $inventoryLotId,
        public float $quantity,
        public float $unitCost
    ) {}
}