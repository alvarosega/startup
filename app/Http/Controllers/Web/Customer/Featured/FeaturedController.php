<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Featured;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Featured\GetProductShowcaseAction;
use App\Actions\Customer\Featured\GetHomeFeaturedAction; // ACCIÓN DE CARRUSEL
use App\Services\ShopContextService;
use App\Http\Resources\Customer\Sku\SkuResource;
use App\Http\Resources\Customer\Featured\FeaturedProductResource;
use Inertia\Inertia;

class FeaturedController extends Controller
{
    public function show(
        string $slug, 
        GetProductShowcaseAction $showcaseAction,
        GetHomeFeaturedAction $featuredAction, // Inyectar carrusel
        ShopContextService $context
    ) {
        $branchId = $context->getActiveBranchId();
        
        // 1. Data del Showcase (Principal + Otros)
        $data = $showcaseAction->execute($slug, $branchId);
        
        // 2. Data del Carrusel (Navegación horizontal entre destacados)
        $featured = $featuredAction->execute($branchId);

        return Inertia::render('Customer/Featured/Show', [
            'featuredProducts' => FeaturedProductResource::collection($featured),
            'showcase' => [
                'product' => $data['product'],
                'skus' => SkuResource::collection($data['skus']),
                'others_paginated' => SkuResource::collection($data['others_paginated'])
            ]
        ]);
    }
}