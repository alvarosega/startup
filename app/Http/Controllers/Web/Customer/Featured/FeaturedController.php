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
        GetHomeFeaturedAction $featuredAction,
        ShopContextService $context
    ): \Inertia\Response {
        $branchId = $context->getActiveBranchId();

        // RECTIFICACIÓN: Debes ejecutar la acción para definir la variable $featured
        $featured = $featuredAction->execute($branchId);

        return Inertia::render('Customer/Featured/Show', [
            // Ahora la variable $featured ya existe y puede ser resuelta
            'featuredProducts' => FeaturedProductResource::collection($featured)->resolve(),

            // 2. DATA PRINCIPAL (Diferida para activar Skeletons)
            'showcase' => Inertia::defer(function() use ($showcaseAction, $slug, $branchId) {
                $data = $showcaseAction->execute($slug, $branchId);
                
                return [
                    'product' => $data['product'],
                    'skus' => SkuResource::collection($data['skus']),
                    'others_paginated' => SkuResource::collection($data['others_paginated'])
                ];
            })
        ]);
    }
}