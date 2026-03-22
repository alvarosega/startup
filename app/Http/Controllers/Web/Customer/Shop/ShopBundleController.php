<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetBundleConfigurationAction;
use App\Http\Resources\Customer\Shop\BundleConfigurationResource; // Blindaje v2.0
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShopBundleController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService
    ) {}

    public function __invoke(
        Request $request, 
        Bundle $bundle, 
        GetBundleConfigurationAction $action
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();

        // Ejecución de la acción atómica
        $bundleData = $action->execute($bundle, $branchId);

        return Inertia::render('Customer/Shop/ConfigurableBundle', [
            // Regla 2.C: Sanitización vía Resource obligatoria
            'bundle' => new BundleConfigurationResource($bundleData),
            'promo_banner' => (string) $request->query('promo_banner'),
        ]);
    }
}