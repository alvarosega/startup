<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Sku;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class SkuDataDTO
{
    public function __construct(
        public string $name,
        public ?string $code,
        public float $price,
        public float $conversionFactor,
        public float $weight,
        public bool $isActive,
        public ?UploadedFile $image = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            code: (string) $request->validated('code'),
            price: (float) $request->validated('price'),
            conversionFactor: (float) $request->validated('conversionFactor'),
            weight: (float) $request->validated('weight'),
            isActive: (bool) $request->validated('isActive'),
            image: $request->file('image')
        );
    }
}