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
            $errors = [];
    
            foreach ($bundle->skus as $sku) {
                $required = (int)($sku->pivot->quantity ?? 1);
    
                // DELEGACIÓN TOTAL: El Service ya valida stock y suma cantidades
                $result = $this->cartService->addSku(
                    skuId: $sku->id,
                    quantity: $required,
                    guestUuid: $guestUuid,
                    isAbsolute: false 
                );
    
                if ($result->success) {
                    $addedCount++;
                } else {
                    $errors[] = "{$sku->name}: {$result->message}";
                }
            }
    
            return (object) [
                'success' => $addedCount > 0,
                'message' => $addedCount > 0 
                    ? "Se actualizaron {$addedCount} ítems del pack." 
                    : "No se pudo añadir el pack: " . ($errors[0] ?? 'Stock agotado'),
                'code' => $addedCount > 0 ? 'SUCCESS' : 'STOCK_FAILURE'
            ];
        });
    }
}