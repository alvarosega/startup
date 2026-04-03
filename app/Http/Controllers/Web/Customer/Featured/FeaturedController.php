<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Featured;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Featured\GetProductShowcaseAction;
use App\Services\ShopContextService;
use App\Http\Resources\Customer\Sku\SkuResource;
use Inertia\Inertia;

class FeaturedController extends Controller
{
    public function show(
        string $slug, 
        GetProductShowcaseAction $action, 
        ShopContextService $context
    ) {
        $branchId = $context->getActiveBranchId();
        $data = $action->execute($slug, $branchId);

        return Inertia::render('Customer/Featured/Show', [
            'showcase' => [
                'product' => $data['product'],
                'skus' => SkuResource::collection($data['skus']),
                'others_paginated' => SkuResource::collection($data['others_paginated'])
            ]
        ]);
    }
}