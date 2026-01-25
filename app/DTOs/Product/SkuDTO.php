<?php

namespace App\DTOs\Product;

class SkuDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $code,
        public readonly float $price, // Precio base
        public readonly float $conversion_factor,
        public readonly float $weight,
        public readonly ?object $image,
    ) {}
}