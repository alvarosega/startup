<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Sku;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class SkuDataDTO
{
    public function __construct(
        public string $name,
        public ?string $code,
        public float $base_price,
        public float $conversion_factor,
        public float $weight,
        public bool $is_active,
        public ?UploadedFile $image = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            code: $validated['code'] ?? null,
            base_price: (float) $validated['base_price'],
            conversion_factor: (float) $validated['conversion_factor'],
            weight: (float) $validated['weight'],
            is_active: $request->boolean('is_active'),
            image: $request->file('image')
        );
    }
}