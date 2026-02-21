<?php
namespace App\DTOs\Admin\Sku;

use Illuminate\Http\UploadedFile;

readonly class SkuDataDTO
{
    public function __construct(
        public string $name,
        public ?string $code,
        public float $price,
        public int $conversionFactor,
        public float $weight,
        public ?UploadedFile $image
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            code: $data['code'] ?? null,
            price: (float) $data['price'],
            conversionFactor: (int) $data['conversion_factor'],
            weight: (float) $data['weight'],
            image: $data['image'] ?? null
        );
    }
}