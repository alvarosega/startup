<?php

namespace App\DTOs\Sku;

class SkuData
{
    public function __construct(
        public readonly ?string $id, // Null si es nuevo
        public readonly string $name,
        public readonly ?string $code,
        public readonly float $weight,
        public readonly float $conversionFactor,
        public readonly float $price, // Precio base
        public readonly bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            code: $data['code'] ?? null,
            weight: (float) ($data['weight'] ?? 0),
            conversionFactor: (float) ($data['conversion_factor'] ?? 1),
            price: (float) ($data['price'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'weight' => $this->weight,
            'conversion_factor' => $this->conversionFactor,
            'is_active' => $this->isActive,
        ];
    }
}