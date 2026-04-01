<?php

declare(strict_types=1);

namespace App\Actions\Customer\Sku;

use App\Models\Sku;
use App\Services\Finance\PriceResolverService;
use App\Services\ShopContextService;
use App\DTOs\Customer\Sku\SkuStateDTO;
use Illuminate\Support\Facades\DB;

class GetSkuStateAction
{
    public function __construct(
        private PriceResolverService $priceResolver,
        private ShopContextService $shopContext
    ) {}

    public function execute(string $skuId, int $qtyInCart): SkuStateDTO
    {
        $branchId = $this->shopContext->getActiveBranchId();
        $now = now();

        $sku = Sku::with(['prices' => function ($q) use ($branchId, $now) {
            $q->where('branch_id', $branchId)
              ->where('valid_from', '<=', $now)
              ->where(fn($sub) => $sub->whereNull('valid_to')->orWhere('valid_to', '>=', $now));
        }])->findOrFail($skuId);

        $stockAvailable = (int) DB::table('inventory_lots')
            ->where('sku_id', $sku->id)
            ->where('branch_id', $branchId)
            ->where('is_safety_stock', false)
            ->sum(DB::raw('quantity - reserved_quantity'));

        $targetQty = $qtyInCart > 0 ? $qtyInCart : 1;
        $priceData = $this->priceResolver->resolveWinningPrice($sku, $targetQty, $now);

        $nextQty = isset($priceData->next_tier['min_quantity']) ? (int) $priceData->next_tier['min_quantity'] : 0;
        $upsellData = $priceData->next_tier ? [
            'next_qty'   => $nextQty,
            'next_price' => (float) $priceData->next_tier['final_price'],
            'needed'     => max(0, $nextQty - $qtyInCart)
        ] : null;

        $canAddMore = ($stockAvailable - $qtyInCart) > 0 && $qtyInCart < 99;

        return new SkuStateDTO(
            final_price: (float) $priceData->final_price,
            list_price: (float) $priceData->list_price,
            stock_available: max(0, $stockAvailable),
            can_add_more: $canAddMore,
            upsell_data: $upsellData,
            is_active: (bool) $sku->is_active
        );
    }
}