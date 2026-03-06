<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopLandingAction;
use Inertia\Inertia;
use Inertia\Response;

class ShopLandingController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function __invoke(GetShopLandingAction $landingAction): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        $landingData = $landingAction->execute($branchId);
        
        return Inertia::render('Customer/Shop/Index', [
            'zonesData'   => $landingData['zones'],
            'bundlesData' => $landingData['bundles'],
        ]);
    }
}