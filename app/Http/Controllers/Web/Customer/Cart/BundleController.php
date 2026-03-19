<?php

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Cart\GetBundleDetailsAction;
use App\Services\Cart\CartService; // <--- Inyectamos el cerebro del carrito
use App\Models\Bundle;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use App\Http\Resources\Customer\Bundle\BundleModalResource;
use App\Actions\Customer\Cart\AddBundleToCartAction; 

class BundleController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService,
        protected CartService $cartService
    ) {}

    public function show(Bundle $bundle, GetBundleDetailsAction $action): JsonResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $bundleWithData = $action->execute($bundle, $branchId);
        return response()->json((new BundleModalResource($bundleWithData))->resolve());
    }

    public function add(Request $request, AddBundleToCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        // El DTO captura automáticamente 'bundle_id' y 'custom_items' del request body
        $dto = \App\DTOs\Customer\Cart\AddBundleDTO::fromRequest($request, $branchId);

        try {
            $action->execute($dto);
            return back()->with('success', 'Pack añadido correctamente.');
        } catch (\Exception $e) {
            // Logueamos el error para auditoría interna
            \Illuminate\Support\Facades\Log::error("Error al añadir bundle: " . $e->getMessage());
            
            return back()->withErrors(['bundle' => $e->getMessage()]);
        }
    }
}