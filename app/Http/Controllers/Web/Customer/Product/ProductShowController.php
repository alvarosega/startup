<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Product;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Product\GetProductVariantsAction;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductShowController extends Controller
{
    public function __invoke(string $id, Request $request, GetProductVariantsAction $action, ShopContextService $shopContext)
    {
        $branchId = $shopContext->getActiveBranchId();
        $data = $action->execute($id, $branchId);

        return Inertia::render('Customer/Product/Show', [
            'product' => $data['product'],
            'variants' => $data['variants'],
            'activeSkuId' => $request->query('active_sku')
        ]);
    }
}