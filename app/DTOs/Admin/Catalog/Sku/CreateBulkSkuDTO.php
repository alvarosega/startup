<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Sku;

use Illuminate\Http\Request;

readonly class CreateBulkSkuDTO
{
    /**
     * @param array<SkuDataDTO> $skus
     */
    public function __construct(
        public array $skus
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();
        $skus = [];

        foreach ($validated['skus'] as $skuData) {
            $skus[] = new SkuDataDTO(
                name: (string) $skuData['name'],
                code: !empty($skuData['code']) ? (string) $skuData['code'] : null,
                base_price: (float) $skuData['base_price'],
                conversion_factor: (float) $skuData['conversion_factor'],
                weight: (float) $skuData['weight'],
                is_active: (bool) $skuData['is_active'],
                image: null
            );
        }

        return new self($skus);
    }
}