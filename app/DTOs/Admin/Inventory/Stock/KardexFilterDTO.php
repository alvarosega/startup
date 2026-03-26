<?php

namespace App\DTOs\Admin\Inventory\Stock;

use Illuminate\Http\Request;

readonly class KardexFilterDTO
{
    public function __construct(
        public string $sku_id,
        public ?string $branch_id,
        public ?string $start_date,
        public ?string $end_date,
        public int $per_page = 50
    ) {}

    public static function fromRequest(Request $request, string $skuId): self
    {
        return new self(
            sku_id:     $skuId,
            branch_id:  $request->query('branch_id'),
            start_date: $request->query('start_date'),
            end_date:   $request->query('end_date'),
            per_page:   (int) $request->query('per_page', 50)
        );
    }
}