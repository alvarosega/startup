<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Bundle;

use Illuminate\Http\UploadedFile;

final readonly class BundleData
{
    /**
     * @param array<string> $skuIds
     */
    public function __construct(
        public string $name,
        public string $type,
        public bool $isActive,
        public ?UploadedFile $image,
        public array $skuIds
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            type: (string) $validated['type'],
            isActive: (bool) $validated['is_active'],
            image: $validated['image'] ?? null,
            skuIds: (array) $validated['sku_ids']
        );
    }
}