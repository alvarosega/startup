<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Brand;

readonly class BrandFilterDTO
{
    public function __construct(
        public string $branchId,
        public int $limit = 12
    ) {}
}