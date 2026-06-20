<?php

declare(strict_types=1);

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\UploadedFile;
use App\Models\Sku;
use App\Models\Category;
use App\Models\Bundle;

final readonly class CreativeData
{
    public function __construct(
        public string $campaignId,
        public string $placementId,
        public string $branchId,
        public ?string $skuId,
        public ?string $categoryId,
        public ?string $bundleId,
        public ?string $brandId,
        public string $targetId,
        public string $targetType,
        public string $name,
        public ?UploadedFile $imageMobile,
        public ?UploadedFile $imageDesktop,
        public string $actionType,
        public int $sortOrder,
        public bool $isActive
    ) {}

    public static function fromRequest(array $validated): self
    {
        // Mapeo inverso estricto de alias a clases polimórficas del sistema
        $targetMap = [
            'sku'      => Sku::class,
            'category' => Category::class,
            'bundle'   => Bundle::class,
        ];

        return new self(
            campaignId: (string) $validated['campaign_id'],
            placementId: (string) $validated['placement_id'],
            branchId: (string) $validated['branch_id'],
            skuId: $validated['sku_id'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            bundleId: $validated['bundle_id'] ?? null,
            brandId: $validated['brand_id'] ?? null,
            targetId: (string) $validated['target_id'],
            targetType: $targetMap[$validated['target_type']],
            name: (string) $validated['name'],
            imageMobile: $validated['image_mobile'] ?? null,
            imageDesktop: $validated['image_desktop'] ?? null,
            actionType: (string) $validated['action_type'] ?? 'ADD_TO_CART',
            sortOrder: (int) ($validated['sort_order'] ?? 0),
            isActive: (bool) ($validated['is_active'] ?? true)
        );
    }
}