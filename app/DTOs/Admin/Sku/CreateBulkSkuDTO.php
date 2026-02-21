<?php

namespace App\DTOs\Admin\Sku;

use Illuminate\Http\Request;

readonly class CreateBulkSkuDTO
{
    /**
     * @param SkuDataDTO[] $skus
     */
    public function __construct(
        public array $skus
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            skus: array_map(
                fn($item) => SkuDataDTO::fromArray($item),
                $request->validated('skus')
            )
        );
    }
}