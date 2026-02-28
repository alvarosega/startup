<?php

namespace App\DTOs\Admin\Inventory;

use Illuminate\Http\Request;

readonly class StockFilterDTO
{
    public function __construct(
        public ?string $branch_id,
        public ?string $search,
        public int $per_page = 15
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            branch_id: $request->query('branch_id'),
            search:    $request->query('search'),
            per_page:  (int) $request->query('per_page', 15)
        );
    }
}