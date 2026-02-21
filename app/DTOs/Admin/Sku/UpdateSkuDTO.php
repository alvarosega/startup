<?php
namespace App\DTOs\Admin\Sku;

use Illuminate\Http\UploadedFile;

readonly class UpdateSkuDTO
{
    public function __construct(
        public ?string $id, // UUID si es edición, null si es nuevo SKU en el update
        public string $name,
        public ?string $code,
        public float $price,
        public float $conversionFactor,
        public float $weight,
        public ?UploadedFile $image
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            code: $data['code'] ?? null,
            price: (float) $data['price'],
            conversionFactor: (float) ($data['conversion_factor'] ?? 1),
            weight: (float) ($data['weight'] ?? 0),
            image: $data['image'] ?? null
        );
    }
}
