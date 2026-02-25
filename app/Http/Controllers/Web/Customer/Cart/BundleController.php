<?php

namespace App\Http\Controllers\Web\Customer\Cart;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Cart\AddBundleToCartAction;
use App\DTOs\Customer\Cart\AddBundleDTO;
use App\Models\Bundle;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};

class BundleController extends Controller
{
    public function __construct(protected ShopContextService $contextService) {}

    public function show(Bundle $bundle): JsonResponse
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        // Cargamos relaciones necesarias para el Modal
        $bundle->load(['skus.product', 'skus.prices' => fn($q) => $q->where('branch_id', $branchId)]);
        
        return response()->json($bundle);
    }

    public function add(Request $request, AddBundleToCartAction $action): RedirectResponse
    {
        $dto = AddBundleDTO::fromRequest($request, $this->contextService->getActiveBranchId());

        try {
            $action->execute($dto);
            return back()->with('success', 'Pack añadido.');
        } catch (\Exception $e) {
            return back()->withErrors(['bundle' => $e->getMessage()]);
        }
    }
}