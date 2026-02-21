<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Actions\Admin\Price\{GetPricingMatrixAction, StorePriceAction};
use App\DTOs\Admin\Price\CreatePriceDTO;
use App\Http\Requests\Admin\Price\StorePriceRequest;
use App\Http\Resources\Admin\Price\PricingMatrixResource;
use Illuminate\Http\{Request, RedirectResponse};
use Inertia\{Inertia, Response as InertiaResponse};

class PriceController extends Controller
{
    /**
     * Renderiza la matriz de precios con filtrado.
     */
    public function index(Request $request, GetPricingMatrixAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Prices/Index', [
            'products' => PricingMatrixResource::collection($action->execute($request)),
            'branches' => Branch::active()->get(['id', 'name']),
            'filters'  => $request->only(['search', 'branch_id'])
        ]);
    }

    /**
     * MÉTODO CORREGIDO: Orquestación del guardado quirúrgico de precios.
     */
    public function store(StorePriceRequest $request, StorePriceAction $action): RedirectResponse
    {
        // 1. Transformación a DTO (Capa de Transporte)
        $dto = CreatePriceDTO::fromRequest($request);

        // 2. Ejecución de Lógica de Negocio (Capa de Acción)
        $action->execute($dto);

        // 3. Respuesta (Capa de Presentación)
        return back()->with('success', 'Estrategia de precio actualizada correctamente.');
    }
}