<?php

namespace App\DTOs\Customer\Shop\Category;

/**
 * Regla 2.A: Inmutabilidad absoluta.
 */
readonly class CategoryPageDTO
{
    public function __construct(
        public string $branchId,
        public string $categoryId
    ) {}

    public static function fromRequest(string $branchId, string $categoryId): self
    {
        return new self($branchId, $categoryId);
    }
}