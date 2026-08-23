<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Category;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Category\GetCategoryDetailsAction;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService,
        private readonly GetCategoryDetailsAction $getCategoryDetailsAction
    ) {}

    public function __invoke(string $category, Request $request): Response
    {
        // Fijación perimetral del contexto logístico de la sucursal
        $this->contextService->getActiveBranchId();

        // Obtención síncrona de metadatos de identidad de la categoría
        $categoryDTO = $this->getCategoryDetailsAction->execute($category);

        return Inertia::render('Customer/Category/Show', [
            'categoryData' => $categoryDTO->toArray(),
            'products'     => [], // Alcance postergado por instrucción de negocio
            'banners'      => [], // Alcance postergado por instrucción de negocio
            'filters'      => $request->only(['search', 'sort']),
        ]);
    }
}