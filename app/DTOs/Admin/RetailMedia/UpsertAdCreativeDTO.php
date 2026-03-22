<?php

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class UpsertAdCreativeDTO
{
    public function __construct(
        public ?string $id,
        public string $campaign_id,
        public string $placement_id,
        public string $branch_id,    // Singular
        public ?string $category_id, // Nuevo
        public string $target_type,
        public string $target_id,
        public string $name,
        public string $action_type,
        public int $sort_order,
        public bool $is_active,
        public ?UploadedFile $image_mobile = null,
        public ?UploadedFile $image_desktop = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $map = [
            'sku'    => \App\Models\Sku::class,
            'bundle' => \App\Models\Bundle::class,
        ];

        return new self(
            id: $request->input('id'),
            campaign_id: $request->input('campaign_id'),
            placement_id: $request->input('placement_id'),
            branch_id: $request->input('branch_id'),
            category_id: $request->input('category_id'),
            target_type: $map[$request->input('target_type')] ?? \App\Models\Sku::class,
            target_id: $request->input('target_id'),
            name: $request->input('name'),
            action_type: $request->input('action_type', 'ADD_TO_CART'),
            sort_order: (int) $request->input('sort_order', 0),
            is_active: $request->boolean('is_active'),
            image_mobile: $request->file('image_mobile'),
            image_desktop: $request->file('image_desktop'),
        );
    }
}