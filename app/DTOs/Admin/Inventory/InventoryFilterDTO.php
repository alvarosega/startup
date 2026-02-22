<?php

namespace App\DTOs\Admin\Inventory;

readonly class InventoryFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $branch_id = null,
        public int $per_page = 20,
        public ?string $cursor = null
    ) {}
}