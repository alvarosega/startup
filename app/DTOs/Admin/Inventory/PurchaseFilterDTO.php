<?php

namespace App\DTOs\Admin\Inventory;

use Illuminate\Http\Request;

readonly class PurchaseFilterDTO
{
    public function __construct(
        public ?string $branch_id,
        public ?string $type,
        public int $per_page = 15
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            branch_id: $request->query('branch_id'),
            type:      $request->query('type'),
            per_page:  (int) $request->query('per_page', 15)
        );
    }
}