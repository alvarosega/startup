<?php

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Cart\{AddBundleToCartAction, GetBundleDetailsAction};
use App\DTOs\Customer\Cart\AddBundleDTO;
use App\Models\Bundle;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use App\Http\Resources\Customer\Bundle\BundleModalResource; // <-- AÑADIR ESTO

class BundleController extends Controller
{
    public function __construct(protected ShopContextService $contextService) {}

    public function show(Bundle $bundle, GetBundleDetailsAction $action): JsonResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        $bundleWithData = $action->execute($bundle, $branchId);
        
        // RESOLVE(): Formatea los datos a la fuerza bruta sin wrappers innecesarios
        return response()->json((new BundleModalResource($bundleWithData))->resolve());
    }

    public function add(Request $request, AddBundleToCartAction $action): RedirectResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        $dto = AddBundleDTO::fromRequest($request, $branchId);

        try {
            $action->execute($dto);
            return back()->with('success', 'Pack añadido correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['bundle' => $e->getMessage()]);
        }
    }
}