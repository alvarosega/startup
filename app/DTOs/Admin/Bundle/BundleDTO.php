<?php

namespace App\DTOs\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class BundleDTO
{
    public function __construct(
        public readonly string $branchId, // CORREGIDO: UUID es string
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?float $fixedPrice,
        public readonly bool $isActive,
        public readonly ?UploadedFile $image,
        public readonly array $items 
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            branchId: $request->validated('branch_id'), 
            name: $request->validated('name'),
            description: $request->validated('description'),
            fixedPrice: $request->validated('fixed_price') ? (float)$request->validated('fixed_price') : null,
            isActive: $request->boolean('is_active'),
            image: $request->file('image'),
            items: $request->validated('items')
        );
    }
}