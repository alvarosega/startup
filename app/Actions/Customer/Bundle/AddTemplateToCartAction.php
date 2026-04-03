<?php

declare(strict_types=1);
namespace App\Actions\Customer\Bundle;

use App\Models\{Bundle, Cart};
use App\Services\Cart\CartService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\DB;

class AddTemplateToCartAction
{
    public function __construct(
        private CartService $cartService,
        private ShopContextService $shopContext
    ) {}

    public function execute(string $bundleId, ?string $guestUuid = null): object
    {
        $branchId = $this->shopContext->getActiveBranchId();
        
        // 1. Carga de Alta Densidad del Template
        $bundle = Bundle::where('id', $bundleId)
            ->where('branch_id', $branchId)
            ->with(['skus' => function($q) use ($branchId) {
                $q->leftJoin('inventory_balances as ib', function($j) use ($branchId) {
                    $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId);
                })->addSelect(['skus.id', 'ib.total_physical', 'ib.total_reserved']);
            }])
            ->firstOrFail();

        return DB::transaction(function () use ($bundle, $guestUuid) {
            $addedCount = 0;
            $ignoredCount = 0;

            foreach ($bundle->skus as $sku) {
                $available = max(0, (int)($sku->total_physical ?? 0) - (int)($sku->total_reserved ?? 0));
                $required = (int)($sku->pivot->quantity ?? 1);

                // PROTOCOLO V.2: No agregar si no hay stock
                if ($available >= $required) {
                    $this->cartService->addSku(
                        skuId: $sku->id,
                        quantity: $required,
                        guestUuid: $guestUuid,
                        isAbsolute: false // Sumamos a lo que ya tenga en el carrito
                    );
                    $addedCount++;
                } else {
                    $ignoredCount++;
                }
            }

            return (object) [
                'success' => $addedCount > 0,
                'message' => "Se agregaron {$addedCount} productos. (" . ($ignoredCount > 0 ? "{$ignoredCount} sin stock" : "Todo listo") . ")",
                'code' => $addedCount > 0 ? 'SUCCESS' : 'NO_STOCK_AVAILABLE'
            ];
        });
    }
}