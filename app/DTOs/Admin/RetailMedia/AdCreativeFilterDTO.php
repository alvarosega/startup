<?php

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\Request;

readonly class AdCreativeFilterDTO
{
    public function __construct(
        public ?string $placement_code = null,
        public ?string $market_zone_id = null,
        public ?string $branch_id = null,
        public ?string $category_id = null, // REPARACIÓN: Propiedad añadida
        public ?bool $is_active = null,
        public int $per_page = 15
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            placement_code: $request->filled('placement_code') ? $request->string('placement_code')->trim()->value() : null,
            market_zone_id: $request->filled('market_zone_id') ? $request->string('market_zone_id')->trim()->value() : null,
            branch_id: $request->filled('branch_id') ? $request->string('branch_id')->trim()->value() : null,
            // MAPEO: Capturamos el filtro de categoría desde la URL
            category_id: $request->filled('category_id') ? $request->string('category_id')->trim()->value() : null,
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            per_page: $request->integer('per_page', 15)
        );
    }
}