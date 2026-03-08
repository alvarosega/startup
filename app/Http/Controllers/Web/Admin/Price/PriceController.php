<?php
namespace App\Http\Controllers\Web\Admin\Price;

use App\Http\Controllers\Controller;
use App\Models\{Branch, Price};
use App\Actions\Admin\Price\{GetPricingMatrixAction, UpsertPriceAction, DeletePriceAction};
use App\DTOs\Admin\Price\PriceData;
use App\Http\Requests\Admin\Price\StorePriceRequest;
use App\Http\Resources\Admin\Price\PricingMatrixResource;
use Illuminate\Http\{Request, RedirectResponse};
use Inertia\{Inertia, Response as InertiaResponse};
use Illuminate\Support\Facades\Auth;

class PriceController extends Controller
{
    private string $guard = 'super_admin';

    public function index(Request $request, GetPricingMatrixAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Prices/Index', [
            'products' => PricingMatrixResource::collection($action->execute($request)),
            'branches' => Branch::active()->get(['id', 'name']),
            'filters'  => $request->only(['search', 'branch_id'])
        ]);
    }

    public function store(StorePriceRequest $request, UpsertPriceAction $action): RedirectResponse
    {
        $adminId = Auth::guard($this->guard)->id();
        $dto = PriceData::fromRequest($request, $adminId);

        $action->execute($dto);

        return back()->with('success', 'Estrategia de precio inyectada al motor.');
    }

    public function destroy(Price $price, DeletePriceAction $action): RedirectResponse
    {
        $adminId = Auth::guard($this->guard)->id();
        $action->execute($price, $adminId);
        
        return back()->with('success', 'Regla de precio anulada.');
    }
}