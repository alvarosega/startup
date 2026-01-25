<?php

namespace App\DTOs\Bundle;

use App\Http\Requests\Bundle\BundleRequest;

class BundleDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?float $fixed_price,
        public readonly bool $is_active,
        public readonly ?string $image_path,
        public readonly array $items
    ) {}

    public static function fromRequest(BundleRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            fixed_price: $request->validated('fixed_price') ? (float) $request->validated('fixed_price') : null,
            is_active: $request->boolean('is_active'),
            image_path: $request->validated('image_path'),
            items: self::prepareItems($request->validated('items'))
        );
    }

    private static function prepareItems(array $items): array
    {
        $prepared = [];
        foreach ($items as $item) {
            $id = $item['sku_id'];
            if (isset($prepared[$id])) {
                $prepared[$id]['quantity'] += $item['quantity'];
            } else {
                $prepared[$id] = ['quantity' => $item['quantity']];
            }
        }
        return $prepared;
    }
}