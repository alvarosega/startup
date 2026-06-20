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
        
        // Si la request trae 'brand_id', pedimos solo esa marca.
        // Si no, cargamos la estructura inicial.
        $brandId = $request->query('brand_id');
    
        $data = $zoneAction->execute($zone, $branchId, $brandId);
    
        return Inertia::render('Customer/Shop/Zone', [
            'zone'              => $data['zone'],
            'brandsNavigation'  => $data['brands_navigation'],
            'brandContent'      => $data['brand_content'], // Será null en el primer load
            'targetCategory'    => $request->query('category'),
        ]);
    }
}