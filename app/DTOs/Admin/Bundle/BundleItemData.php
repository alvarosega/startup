<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Bundle;

readonly class BundleItemData
{
    public function __construct(
        public string $sku_id,
        public float $quantity
    ) {}
}