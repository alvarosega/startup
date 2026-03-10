<?php
namespace App\DTOs\Admin\Sku;

use Illuminate\Http\UploadedFile;

readonly class SkuDataDTO
{
    public function __construct(
        public string $name,
        public ?string $code,
        public float $price, // Esto mapeará el base_price interno
        public float $conversionFactor,
        public float $weight,
        public bool $isActive,
        public ?UploadedFile $image
    ) {}

    // Usado por SkuController@update (Edición Individual)
    public static function fromRequest($request): self
    {
        return new self(
            name:             $request->validated('name'),
            code:             $request->validated('code'),
            price:            (float) $request->validated('base_price'),
            conversionFactor: (float) $request->validated('conversion_factor'),
            weight:           (float) $request->validated('weight'),
            isActive:         $request->boolean('is_active', true),
            image:            $request->file('image')
        );
    }

    // Usado por CreateBulkSkuDTO (Creación en Lote)
    public static function fromArray(array $data): self
    {
        return new self(
            name:             $data['name'],
            code:             $data['code'] ?? null,
            price:            (float) ($data['base_price'] ?? 0),
            conversionFactor: (float) ($data['conversion_factor'] ?? 1),
            weight:           (float) ($data['weight'] ?? 0),
            isActive:         true, // Por defecto en creación
            image:            $data['image'] ?? null
        );
    }
}