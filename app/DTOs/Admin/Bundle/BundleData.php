<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class BundleData
{
    /**
     * @param BundleItemData[] $items
     */
    public function __construct(
        public string $name,
        public ?UploadedFile $image,
        public string $type,
        public ?string $starts_at,
        public ?string $ends_at,
        public bool $is_active,
        public array $items
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        $items = array_map(
            fn(array $item) => new BundleItemData(
                sku_id: (string) $item['sku_id'],
                quantity: (float) $item['quantity']
            ),
            $validated['items'] ?? []
        );

        return new self(
            name: trim((string) $validated['name']),
            image: $request->file('image'),
            type: (string) ($validated['type'] ?? 'OFFER'),
            starts_at: $validated['starts_at'] ?? null,
            ends_at: $validated['ends_at'] ?? null,
            is_active: (bool) ($validated['is_active'] ?? true),
            items: $items
        );
    }
}