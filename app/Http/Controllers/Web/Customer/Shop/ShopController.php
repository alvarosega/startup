<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\Customer\Queries\CategoryQueryService;
use App\Services\ShopContextService;
use Inertia\Inertia;
use Inertia\Response;

final class ShopController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService,
        private readonly CategoryQueryService $categoryQueryService
    ) {}

    public function __invoke(): Response
    {
        // Forzado del ciclo de vida del contexto comercial en la sesión
        $this->contextService->getActiveBranchId();

        return Inertia::render('Customer/Shop/Index', [
            // Contrato de datos local exacto para el carrusel de la interfaz
            'categories' => Inertia::defer(fn (): array => 
                $this->categoryQueryService->getRootCategoriesForCarousel()
            ),
        ]);
    }
}