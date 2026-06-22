<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Price;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Sku;
use App\Models\Operations\Branch;
use App\Http\Resources\Admin\Inventory\PriceResource;
use App\Http\Requests\Admin\Inventory\Price;
use App\DTOs\Admin\Inventory\Price\MassPriceData;
use App\Actions\Admin\Inventory\Price\StoreMassPrices;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PriceController extends Controller
{
    public function index(): Response
    {
        // Carga optimizada de la matriz comercial activa
        $prices = Price::with(['sku', 'branch'])
            ->where('deleted_epoch', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $branches = Branch::where('deleted_epoch', 0)->get(['id', 'name']);
        
        $skus = Sku::whereNull('deleted_at')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Prices/Index', [
            'prices'   => PriceResource::collection($prices)->resolve(),
            'branches' => $branches->map(fn($b) => ['id' => $b->id, 'name' => mb_toUpperCase($b->name)]),
            'skus'     => $skus->map(fn($s) => ['id' => $s->id, 'name' => mb_toUpperCase($s->name), 'code' => $s->code])
        ]);
    }

    public function store(Price\StorePriceRequest $request, StoreMassPrices $action): RedirectResponse
    {
        $action->execute(MassPriceData::fromRequest($request));

        return redirect()->route('admin.prices.index')
            ->with('success', 'SINC_MATRIZ: Estrategia de precios propagada con éxito sobre las variantes seleccionadas.');
    }

    public function destroy(Price $price): RedirectResponse
    {
        // Soft delete controlado bajo protocolo epoch
        $price->update([
            'deleted_epoch' => time(),
            'deleted_at'    => now()
        ]);

        return redirect()->back()
            ->with('success', 'REGLA_PRECIOS: Celda de precio dada de baja (Soft Delete).');
    }
}