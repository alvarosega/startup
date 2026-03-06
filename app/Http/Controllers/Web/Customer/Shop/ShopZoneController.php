<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketZone;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopZoneAction;
use Inertia\Inertia;
use Inertia\Response;

class ShopZoneController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function __invoke(Request $request, MarketZone $zone, GetShopZoneAction $zoneAction): Response 
    {
        $branchId = $this->contextService->getActiveBranchId();

        $data = $zoneAction->execute($zone, $branchId);

        return Inertia::render('Customer/Shop/Zone', [
            'zone'              => $data['zone'],
            'groupedCategories' => $data['groupedCategories'],
            'targetCategory'    => $request->query('category'),
            'shop_context'      => ['branch_id' => $branchId] 
        ]);
    }
}