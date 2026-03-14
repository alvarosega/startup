<?php

namespace App\Http\Controllers\Web\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Requests & DTOs
use App\Http\Requests\Admin\Driver\UpsertDriverRequest;
use App\DTOs\Admin\Driver\UpsertDriverDTO;

// Actions
use App\Actions\Admin\Driver\UpsertDriverAction;
use App\Actions\Admin\Driver\GetPaginatedDriversAction;
use App\Actions\Admin\Branch\GetActiveBranchesListAction; // <--- USAR SIEMPRE

// Resources
use App\Http\Resources\Admin\Driver\{DriverResource, DriverEditResource};

class DriverController extends Controller
{
    public function index(Request $request, GetPaginatedDriversAction $action)
    {
        $filters = $request->only(['search', 'status']);
        $paginator = $action->execute($filters);

        return Inertia::render('Admin/Drivers/Index', [
            'drivers'       => DriverResource::collection($paginator),
            'filters'       => $filters,
            'pending_count' => Driver::where('status', 'pending')->count()
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
        $action->execute(UpsertDriverDTO::fromRequest($request));

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Conductor registrado exitosamente.');
    }

    public function edit(string $id, GetActiveBranchesListAction $getBranchesAction)
    {
        // Cargamos relación 'profile' sincronizada con la base de datos
        $driver = Driver::with(['profile', 'branch'])->findOrFail($id);
        
        return Inertia::render('Admin/Drivers/Edit', [
            'driver'   => new DriverEditResource($driver),
            'branches' => $getBranchesAction->execute() // <--- Consistencia aplicada
        ]);
    }

    public function update(UpsertDriverRequest $request, string $id, UpsertDriverAction $action)
    {
        $action->execute(UpsertDriverDTO::fromRequest($request, $id));

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Perfil y Estado actualizados de forma atómica.');
    }
}