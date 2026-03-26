<?php
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

    /**
     * Usado por CreateBulkSkuDTO para mapear el lote.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            code: $data['code'] ?? null,
            price: (float) ($data['base_price'] ?? 0), // Alineado con base_price del Form
            conversionFactor: (float) ($data['conversion_factor'] ?? 1),
            weight: (float) ($data['weight'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true)
        );
    }

    /**
     * Usado por SkuController@update para edición individual.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            code: $request->validated('code'),
            price: (float) $request->validated('base_price'),
            conversionFactor: (float) $request->validated('conversion_factor'),
            weight: (float) $request->validated('weight'),
            isActive: (bool) $request->boolean('is_active'),
            image: $request->file('image')
        );
    }
}