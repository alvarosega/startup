<?php

namespace App\DTOs\Shop;

use Illuminate\Http\Request;

class CatalogQueryDTO
{
    public function __construct(
        public readonly int $branchId, // El contexto calculado
        public readonly ?string $search,
        public readonly ?int $categoryId,
        public readonly bool $inStockOnly,
        public readonly int $perPage = 12
    ) {}

    public static function fromRequest(Request $request, int $branchId): self
    {
        return new self(
            branchId: $branchId,
            search: $request->input('search'),
            categoryId: $request->input('category_id'),
            inStockOnly: $request->boolean('in_stock', false), // Por defecto false para mostrar todo
            perPage: 12
        );
    }
}