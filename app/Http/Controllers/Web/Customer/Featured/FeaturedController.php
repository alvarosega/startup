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
            'featuredProducts' => FeaturedProductResource::collection($featured)->resolve(),

            // 2. DATA PRINCIPAL BIEN ESTRUCTURADA (Satisface los contratos de Vue)
            'showcase' => Inertia::defer(function() use ($showcaseAction, $slug, $branchId) {
                $data = $showcaseAction->execute($slug, $branchId);
                
                return [
                    'product' => [
                        'id'          => (string) $data['product']->id,
                        'name'        => (string) $data['product']->name,
                        'slug'        => (string) $data['product']->slug,
                        'description' => (string) $data['product']->description,
                    ],
                    'skus' => SkuResource::collection($data['skus'])->resolve(),
                    'others_paginated' => [
                        'data'        => SkuResource::collection($data['others_paginated']->items())->resolve(),
                        'next_cursor' => $data['others_paginated']->nextCursor() ? $data['others_paginated']->nextCursor()->encode() : null,
                    ]
                ];
            })
        ]);
    }
}