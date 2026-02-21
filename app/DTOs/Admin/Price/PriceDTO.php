<?php
namespace App\DTOs\Admin\Price;

use Carbon\Carbon;
use Illuminate\Http\Request;

readonly class PriceDTO
{
    public function __construct(
        public string $skuId,
        public ?string $branchId,
        public float $listPrice,
        public float $finalPrice,
        public string $type,
        public int $minQuantity,
        public Carbon $validFrom,
        public ?Carbon $validTo
    ) {}

    public static function fromRequest(Request $request): self
    {
        $finalPrice = (float) $request->validated('final_price');

        return new self(
            skuId: (string) $request->validated('sku_id'),
            branchId: $request->validated('branch_id'), // NULL o UUID String
            listPrice: (float) ($request->validated('list_price') ?? $finalPrice),
            finalPrice: $finalPrice,
            type: (string) $request->validated('type', 'regular'),
            minQuantity: (int) $request->validated('min_quantity', 1),
            validFrom: $request->date('valid_from') ?? now(),
            validTo: $request->date('valid_to')
        );
    }
}