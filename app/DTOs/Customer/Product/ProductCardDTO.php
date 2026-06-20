<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Product;

readonly class ProductCardDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brand_name,
        public string $image,
        public float $final_price,
        public float $list_price,
        public float $discount_percentage,
        public int $stock,
        public bool $is_alcoholic,
        public ?array $next_tier = null // ['min_qty' => int, 'price' => float]
    ) {}
}