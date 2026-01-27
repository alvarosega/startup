<?php

namespace App\DTOs\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class BundleDTO
{
    public function __construct(
        public readonly int $branchId, // <--- NUEVO
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?float $fixedPrice,
        public readonly bool $isActive,
        public readonly ?UploadedFile $image,
        public readonly array $items // Array de ['sku_id', 'quantity']
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            // Asumimos que el Admin crea bundles para SU sucursal asignada
            branchId: $request->validated('branch_id'), 
            
            name: $request->validated('name'),
            description: $request->validated('description'),
            fixedPrice: $request->validated('fixed_price'),
            isActive: $request->boolean('is_active'),
            image: $request->file('image'),
            items: $request->validated('items')
        );
    }
}