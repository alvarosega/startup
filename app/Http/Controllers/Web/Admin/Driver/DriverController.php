<?php

namespace App\Http\Controllers\Web\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Requests & DTOs
use App\Http\Requests\Admin\Driver\UpsertDriverRequest;
use App\DTOs\Admin\Driver\UpsertDriverDTO;

// Actions
use App\Actions\Admin\Driver\UpsertDriverAction;
use App\Actions\Admin\Driver\GetPaginatedDriversAction;
use App\Actions\Admin\Branch\GetActiveBranchesListAction;

// Resources
use App\Http\Resources\Admin\Driver\DriverResource;

class DriverController extends Controller
{
    public function index(Request $request, GetPaginatedDriversAction $action)
    {
        $filters = $request->only(['search', 'status']);
        $paginator = $action->execute($filters);

        // Ya no buscamos en details, tu migración dice que 'status' está en la tabla drivers
        $pendingCount = Driver::where('status', 'pending')->count();

        return Inertia::render('Admin/Drivers/Index', [
            'drivers'       => DriverResource::collection($paginator),
            'filters'       => $filters,
            'pending_count' => $pendingCount
        ]);
    }

    public function create(GetActiveBranchesListAction $getBranchesAction)
    {
        return Inertia::render('Admin/Drivers/Create', [
            'branches' => $getBranchesAction->execute()
        ]);
    }

    public function store(UpsertDriverRequest $request, UpsertDriverAction $action)
    {
        $dto = UpsertDriverDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Conductor registrado exitosamente.');
    }

    public function edit(string $id)
    {
        $driver = Driver::with('details')->findOrFail($id);
        
        return Inertia::render('Admin/Drivers/Edit', [
            // REGLA 2.C: Cero modelos crudos al frontend
            'driver' => new \App\Http\Resources\Admin\Driver\DriverEditResource($driver),
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
        ]);
    }
    public function update(UpsertDriverRequest $request, string $id, UpsertDriverAction $action)
    {
        $dto = UpsertDriverDTO::fromRequest($request, $id);
        $action->execute($dto);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Perfil y Estado del conductor actualizados de forma atómica.');
    }
}