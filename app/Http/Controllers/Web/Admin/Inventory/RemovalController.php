<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Removal\StoreRemovalRequest;
use App\DTOs\Admin\Inventory\Removal\RemovalDataDTO;
use App\Actions\Admin\Inventory\Removal\ListRemovalsAction;
use App\Actions\Admin\Inventory\Removal\GetRemovalFormOptionsAction;
use App\Actions\Admin\Inventory\Removal\ApplyRemovalAction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class RemovalController extends Controller
{
    public function index(Request $request, ListRemovalsAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Removals/Index', [
            'removals' => $action->execute($request),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(GetRemovalFormOptionsAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Removals/Create', $action->execute());
    }

    public function store(StoreRemovalRequest $request, ApplyRemovalAction $action): RedirectResponse
    {
        $dto = RemovalDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->route('admin.removals.index')
            ->with('success', 'La declaración de merma ha sido procesada. Stock de lotes y balances decrementados en tiempo real.');
    }
}