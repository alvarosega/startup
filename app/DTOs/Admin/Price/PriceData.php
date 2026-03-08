<?php
namespace App\DTOs\Admin\Price;

use Carbon\Carbon;
use Illuminate\Http\Request;

readonly class PriceData
{
    public function __construct(
        public string $skuId,
        public string $branchId,
        public string $type,
        public float $listPrice,
        public float $finalPrice,
        public int $minQuantity,
        public Carbon $validFrom,
        public ?Carbon $validTo,
        public ?string $adminId // Injected at Controller level, not from request
    ) {}

    public static function fromRequest(Request $request, ?string $adminId): self
    {
        return new self(
            skuId:       $request->validated('sku_id'),
            branchId:    $request->validated('branch_id'),
            type:        $request->validated('type'),
            listPrice:   (float) $request->validated('list_price'),
            finalPrice:  (float) $request->validated('final_price'),
            minQuantity: (int) $request->validated('min_quantity'),
            validFrom:   Carbon::parse($request->validated('valid_from')),
            validTo:     $request->validated('valid_to') ? Carbon::parse($request->validated('valid_to')) : null,
            adminId:     $adminId
        );
    }
}