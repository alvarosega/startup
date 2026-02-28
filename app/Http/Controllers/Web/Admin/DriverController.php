<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Http\Requests\Admin\Driver\CreateDriverRequest;
use App\Http\Requests\Admin\Driver\UpdateDriverRequest;
use App\DTOs\Admin\Driver\CreateDriverData;
use App\DTOs\Admin\Driver\UpdateDriverData;
use App\Actions\Admin\Driver\CreateDriverAction;
use App\Actions\Admin\Driver\UpdateDriverAction;
use App\Actions\Admin\Driver\GetPaginatedDriversAction;
use App\Http\Resources\Admin\Driver\DriverResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index(Request $request, GetPaginatedDriversAction $action)
    {
        $filters = $request->only(['search', 'status']);

        $paginator = $action->execute($filters);

        // Contabilizar pendientes
        $pendingCount = Driver::whereHas('details', fn($q) => $q->where('verification_status', 'pending'))->count();

        return Inertia::render('Admin/Drivers/Index', [
            // Al devolver el ResourceCollection directamente, Inertia lo mapea a la estructura { data, links, meta }
            'drivers'       => DriverResource::collection($paginator),
            'filters'       => $filters,
            'pending_count' => $pendingCount
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Drivers/Create');
    }

    public function store(CreateDriverRequest $request, CreateDriverAction $action)
    {
        $data = CreateDriverData::fromRequest($request);
        
        $action->execute($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Conductor registrado exitosamente.');
    }

    public function edit(string $id)
    {
        $driver = Driver::with('details')->findOrFail($id);
        
        return Inertia::render('Admin/Drivers/Edit', [
            // EL FIX: resolve() extrae el array crudo, eliminando el envoltorio 'data' inyectado por Laravel.
            'driver' => (new DriverResource($driver))->resolve()
        ]);
    }

    public function update(UpdateDriverRequest $request, string $id, UpdateDriverAction $action)
    {
        $driver = Driver::findOrFail($id);
        $data = UpdateDriverData::fromRequest($request);
        
        $action->execute($driver, $data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Perfil de conductor actualizado y sincronizado.');
    }
}