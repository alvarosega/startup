<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Price\StorePriceRequest;
use App\DTOs\Admin\Inventory\Price\PriceDataDTO;
use App\Actions\Admin\Inventory\Price\ListPricesAction;
use App\Actions\Admin\Inventory\Price\GetPriceFormOptionsAction;
use App\Actions\Admin\Inventory\Price\UpsertPriceAction;
use App\Models\Inventory\Price;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PriceController extends Controller
{
    public function index(Request $request, ListPricesAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Prices/Index', [
            'prices' => $action->execute($request),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(GetPriceFormOptionsAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Prices/Create', $action->execute());
    }

    public function store(StorePriceRequest $request, UpsertPriceAction $action): RedirectResponse
    {
        $dto = PriceDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->route('admin.prices.index')
            ->with('success', 'Estructura tarifaria comercial sincronizada y validada correctamente.');
    }

    public function destroy(Price $price): RedirectResponse
    {
        // La baja lógica por Soft Delete activa de forma automatizada el reajuste del deleted_epoch a través del boot del Modelo
        $price->delete();

        return redirect()->back()
            ->with('success', 'La tarifa comercial ha sido dada de baja legítimamente, liberando el segmento cronológico.');
    }
}