<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Sku;

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
        $skus = [];
        foreach ($request->input('skus', []) as $skuData) {
            $skus[] = new SkuDataDTO(
                name: (string) $skuData['name'],
                code: !empty($skuData['code']) ? (string) $skuData['code'] : null,
                price: (float) $skuData['price'],
                conversionFactor: (float) $skuData['conversionFactor'],
                weight: (float) $skuData['weight'],
                isActive: (bool) $skuData['isActive']
            );
        }

        return new self($skus);
    }
}