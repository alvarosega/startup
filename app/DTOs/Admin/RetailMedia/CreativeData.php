<?php

declare(strict_types=1);

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CreativeData
{
    public function __construct(
        public string $campaign_id,
        public string $placement_id,
        public string $branch_id,
        public ?string $sku_id,
        public ?string $category_id,
        public ?string $brand_id,
        public ?string $bundle_id,
        public string $name,
        public ?UploadedFile $image_mobile,
        public ?UploadedFile $image_desktop,
        public string $action_type,
        public int $sort_order,
        public bool $is_active
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            campaign_id: (string) $validated['campaign_id'],
            placement_id: (string) $validated['placement_id'],
            branch_id: (string) $validated['branch_id'],
            sku_id: $validated['sku_id'] ?? null,
            category_id: $validated['category_id'] ?? null,
            brand_id: $validated['brand_id'] ?? null,
            bundle_id: $validated['bundle_id'] ?? null,
            name: trim((string) $validated['name']),
            image_mobile: $request->file('image_mobile'),
            image_desktop: $request->file('image_desktop'),
            action_type: (string) ($validated['action_type'] ?? 'ADD_TO_CART'),
            sort_order: (int) ($validated['sort_order'] ?? 0),
            is_active: (bool) ($validated['is_active'] ?? true)
        );
    }
}