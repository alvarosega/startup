<?php

namespace App\DTOs\Admin\Inventory;

use Illuminate\Http\Request;

readonly class StockFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?string $branch_id,
        public ?string $provider_id,
        public ?string $brand_id,
        public ?string $category_id,
        public ?string $status,
        public int $per_page = 15
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search:      $request->query('search'),
            branch_id:   $request->query('branch_id'),
            provider_id: $request->query('provider_id'),
            brand_id:    $request->query('brand_id'),
            category_id: $request->query('category_id'),
            status:      $request->query('status'),
            per_page:    (int) $request->query('per_page', 15)
        );
    }
}