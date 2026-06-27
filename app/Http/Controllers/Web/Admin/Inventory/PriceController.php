<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StorePriceRequest;
use App\DTOs\Admin\Inventory\PriceDataDTO;
use App\Actions\Admin\Inventory\Price\ListPricesAction;
use App\Actions\Admin\Inventory\Price\UpsertPriceAction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PriceController extends Controller
{
    public function index(Request $request, ListPricesAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Prices/Index', $action->execute($request));
    }

    public function store(StorePriceRequest $request, UpsertPriceAction $action): RedirectResponse
    {
        $dto = PriceDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->back()
            ->with('success', 'Estructura tarifaria comercial sincronizada y versionada en base de datos.');
    }
}