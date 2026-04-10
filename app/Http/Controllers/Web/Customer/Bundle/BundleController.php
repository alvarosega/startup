<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Bundle;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Services\ShopContextService;
use App\Actions\Customer\Cart\GetCustomerCartAction; 
use App\Actions\Customer\Bundle\GetActiveBundlesAction; 
use App\Http\Resources\Customer\Bundle\BundleResource;
use App\Services\Finance\PriceResolverService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class BundleController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService,
        private readonly GetCustomerCartAction $getCartAction,
        private readonly GetActiveBundlesAction $bundlesAction
    ) {}

    public function __invoke(string $slug): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        // Garantizamos que el UUID y el ID de cliente estén disponibles para los cierres (closures)
        $guestUuid = request()->header('X-Guest-UUID') ?? session('guest_client_uuid');
        $customerId = Auth::guard('customer')->id();
        $now = now();

        return Inertia::render('Customer/Bundle/TemplateShow', [
            
            // 1. CARGA DIFERIDA DEL BUNDLE (Mantiene toda tu lógica de Query y RAM)
            'bundle' => Inertia::defer(function() use ($slug, $branchId, $now) {
                $bundle = Bundle::where('branch_id', $branchId)
                ->where('slug', $slug)
                ->active()
                ->with([
                    'skus' => function($q) use ($branchId) {
                        // RECTIFICACIÓN: Join directo para evitar errores de tipo UUID y N+1
                        $q->leftJoin('inventory_balances as ib', function ($j) use ($branchId) {
                            $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId);
                        })
                        ->select('skus.*', 'ib.total_physical', 'ib.total_reserved', 'ib.total_safety');
                    },
                    'skus.product.brand',
                    'skus.prices' => fn($q) => $q->where('branch_id', $branchId)->where('valid_from', '<=', $now),
                ])
                ->firstOrFail();

                // Hidratación en RAM exacta a tu requerimiento
                $bundle->skus->each(function($sku) use ($now) {
                    // RECTIFICACIÓN: Incluimos total_safety para consistencia con el resto del sistema
                    $sku->available_stock = max(0, 
                        (int)($sku->total_physical ?? 0) - 
                        (int)($sku->total_reserved ?? 0) - 
                        (int)($sku->total_safety ?? 0)
                    );
                
                    $sku->resolved_price = app(PriceResolverService::class)
                        ->resolveWinningPrice($sku, 1, $now);
                });
                
                return new BundleResource($bundle);
            }),

            // 2. CARGA DIFERIDA DEL CARRUSEL (No bloquea la vista principal)
            'templateBundles' => Inertia::defer(fn() => 
                BundleResource::collection($this->bundlesAction->execute($branchId, null, 'template'))
            ),

            // 3. CARGA DIFERIDA DEL ESTADO DEL CARRITO (Para SkuCard)
            'currentCart' => Inertia::defer(function() use ($guestUuid, $customerId, $branchId) {
                $cart = $this->getCartAction->execute($guestUuid, $customerId, $branchId);
                return collect($cart['items'] ?? [])
                    ->keyBy('sku_id')
                    ->map(fn($i) => ['qty' => $i['quantity']]);
            }),
        ]);
    }
}