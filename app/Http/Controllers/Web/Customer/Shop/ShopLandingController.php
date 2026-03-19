<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopLandingAction;
use App\DTOs\Customer\Shop\LandingQueryDTO;
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
        
        $dto = LandingQueryDTO::fromRequest($branchId);
        
        $data = $landingAction->execute($dto);
        
        return Inertia::render('Customer/Shop/Index', [
            'zonesData'   => $data['zones'],
            'bundlesData' => $data['bundles'],
            'categories'  => $data['categories']
        ]);
    }
}