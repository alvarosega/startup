<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Bundle;

use App\Http\Controllers\Controller;
use App\Models\{Bundle, CartItem};
use App\Services\ShopContextService;
use App\Actions\Customer\RetailMedia\GetActiveAdCreativesAction;
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use App\Http\Resources\Customer\Bundle\BundleResource;
use App\Actions\Customer\Cart\GetCustomerCartAction; // Inyectamos el lector de carrito
use Inertia\Inertia;
use Inertia\Response;

class BundleController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService,
        private readonly GetActiveAdCreativesAction $adAction,
        private readonly GetCustomerCartAction $getCartAction
    ) {}

    public function __invoke(string $slug): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        $guestUuid = request()->header('X-Guest-UUID') ?? session('guest_client_uuid');

        $bundle = Bundle::where('branch_id', $branchId)
            ->where('slug', $slug)
            ->active()
            ->with([
                'skus.product',
                'skus.prices' => fn($q) => $q->where('branch_id', $branchId),
                'skus.inventoryLots' => fn($q) => $q->where('branch_id', $branchId)->where('is_safety_stock', false)
            ])
            ->firstOrFail();

        // Obtenemos el carrito actual para sincronizar cantidades iniciales
        $cart = $this->getCartAction->execute($guestUuid);
        
        // Mapeamos solo las cantidades de los SKUs que pertenecen a este bundle
        $cartState = collect($cart['items'] ?? [])
        ->mapWithKeys(fn($item) => [
            $item['sku_id'] => [
                'qty'   => (int) $item['quantity'],
                'price' => (float) $item['unit_price'] // Viene calculado por el PriceResolver en el Resource
            ]
        ])
        ->toArray();

        $banners = $this->adAction->execute($branchId, 'BUNDLE_HERO', $bundle->id);
        $view = ($bundle->type === 'atomic') ? 'Customer/Bundle/AtomicShow' : 'Customer/Bundle/TemplateShow';

        return Inertia::render($view, [
            'bundle'        => new BundleResource($bundle),
            'bundleBanners' => HeroBannerResource::collection($banners),
            'currentCart'   => $cartState // Fuente de verdad para la reactividad
        ]);
    }
}