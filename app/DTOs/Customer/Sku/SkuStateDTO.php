<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Sku;

readonly class SkuStateDTO
{
    public function __construct(
        public float $final_price,
        public float $list_price,
        public int $stock_available,
        public bool $can_add_more,
        public ?array $upsell_data,
        public bool $is_active
    ) {}
}