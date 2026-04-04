<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Bundle;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Services\ShopContextService;
use App\Actions\Customer\Cart\GetCustomerCartAction; 
use App\Actions\Customer\Bundle\GetActiveBundlesAction; 
use App\Http\Resources\Customer\Bundle\BundleResource;
use Inertia\Inertia;
use Inertia\Response;

class BundleController extends Controller
{
    // LEY DE ÚNICA INSTANCIA: Inyectamos todo en el constructor
    public function __construct(
        private readonly ShopContextService $contextService,
        private readonly GetCustomerCartAction $getCartAction,
        private readonly GetActiveBundlesAction $bundlesAction
    ) {}

    public function __invoke(string $slug): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        $guestUuid = request()->header('X-Guest-UUID') ?? session('guest_client_uuid');
        $now = now();
    
        // 1. Carga del Bundle Actual
        $bundle = Bundle::where('branch_id', $branchId)
            ->where('slug', $slug)
            ->active()
            ->with([
                'skus.product',
                'skus.prices' => fn($q) => $q->where('branch_id', $branchId)->where('valid_from', '<=', $now),
                'skus' => fn($q) => $q->leftJoin('inventory_balances as ib', function ($j) use ($branchId) {
                    $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId);
                })->addSelect(['skus.id', 'ib.total_physical', 'ib.total_reserved'])
            ])
            ->firstOrFail();
    
        // 2. RECTIFICACIÓN: No excluimos el actual para mostrarlo en el carrusel
        $allTemplateBundles = $this->bundlesAction->execute($branchId, null, 'template');

        // Hidratación en RAM
        $bundle->skus->each(function($sku) use ($now) {
            $sku->max_stock = max(0, (int)($sku->total_physical ?? 0) - (int)($sku->total_reserved ?? 0));
            $sku->resolved_price = app(\App\Services\Finance\PriceResolverService::class)
                ->resolveWinningPrice($sku, 1, $now);
        });

        $cart = $this->getCartAction->execute($guestUuid, auth()->id(), $branchId);

        return Inertia::render('Customer/Bundle/TemplateShow', [
            'bundle'           => new BundleResource($bundle),
            // CAMBIO: El nombre debe coincidir con el prop de Vue
            'templateBundles'  => BundleResource::collection($allTemplateBundles), 
            'currentCart'      => collect($cart['items'] ?? [])->keyBy('sku_id')->map(fn($i) => ['qty' => $i['quantity']])
        ]);
    }
}