<?php

namespace App\DTOs\Customer\Shop;

use Illuminate\Http\Request;

readonly class CatalogQueryDTO
{
    public function __construct(
        public ?string $search,
        public ?string $categoryId,
        public string $branchId
    ) {}

    public static function fromRequest(Request $request, string $branchId): self
    {
        return new self(
            search: $request->query('search'),
            categoryId: $request->query('category_id'),
            branchId: $branchId
        );
    }
}