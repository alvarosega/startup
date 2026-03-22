<?php

namespace App\DTOs\Customer\Shop;

use Illuminate\Http\Request;

readonly class CatalogQueryDTO
{
    public function __construct(
        public string $branchId,
        public ?string $search = null,
        public ?string $categoryId = null,
        public ?string $type = null,
    ) {}

    public static function fromRequest(Request $request, string $branchId): self
    {
        return new self(
            branchId: $branchId,
            search: $request->query('search'),
            categoryId: $request->query('category_id'),
            type: $request->query('type')
        );
    }
}