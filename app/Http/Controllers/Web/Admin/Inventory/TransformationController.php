<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StoreTransformationRequest;
use App\DTOs\Admin\Inventory\TransformationDataDTO;
use App\Actions\Admin\Inventory\Transformation\GetTransformationFormOptionsAction;
use App\Actions\Admin\Inventory\Transformation\TransformInventoryAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TransformationController extends Controller
{
    public function index(GetTransformationFormOptionsAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Transformations/Workspace', $action->execute());
    }

    public function store(StoreTransformationRequest $request, TransformInventoryAction $action): RedirectResponse
    {
        $dto = TransformationDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->back()
            ->with('success', 'Conversión atómica completada. Costos consolidados y capas FIFO inyectadas de forma secuencial.');
    }
}