<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Featured;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Featured\GetProductShowcaseAction;
use App\Http\Resources\Customer\Featured\ProductShowcaseResource;
use App\Services\ShopContextService;
use Inertia\{Inertia, Response};

final class FeaturedController extends Controller
{
    /**
     * Inyección de dependencias vía constructor para asegurar
     * la disponibilidad de $this->shopContext.
     */
    public function __construct(
        private readonly ShopContextService $shopContext
    ) {}

    public function show(string $slug, GetProductShowcaseAction $action): Response
    {
        // El servicio ya es accesible a través de la propiedad de clase
        $branchId = $this->shopContext->getActiveBranchId();
        
        $data = $action->execute($slug, $branchId);

        return Inertia::render('Customer/Featured/Show', [
            'showcase' => new ProductShowcaseResource($data)
        ]);
    }
}