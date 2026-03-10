<?php

namespace App\Http\Controllers\Web\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch;

// Requests & DTOs
use App\Http\Requests\Admin\Users\Customer\UpsertCustomerRequest;
use App\DTOs\Admin\Users\Customer\UpsertCustomerDTO;

// Actions
use App\Actions\Admin\Users\Customer\GetCustomersListAction;
use App\Actions\Admin\Users\Customer\GetCustomerForEditAction;
use App\Actions\Admin\Users\Customer\UpsertCustomerAction;
use App\Actions\Admin\Users\Customer\DeleteCustomerAction;

// Resources
use App\Http\Resources\Admin\User\UserResource;
use App\Http\Resources\Admin\User\UserEditResource;

// Services
use App\Services\Geo\BranchCoverageService;

class CustomerController extends Controller
{
    /**
     * Listado de Clientes (Silo Único - Cacheado)
     */
    public function index(Request $request, GetCustomersListAction $action)
    {
        $data = $action->execute($request->only(['search', 'branch_id']));
        
        return Inertia::render('Admin/Users/Index', [
            'users'    => UserResource::collection($data['users']),
            'branches' => $data['branches'],
            'filters'  => $data['filters']
        ]);
    }

    /**
     * Formulario de Nuevo Cliente (Con Mapa Logístico)
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            // Solo enviamos sucursales activas al mapa
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
        ]);
    }

    /**
     * Registro Atómico de Cliente (DTO + Upsert Action)
     */
    public function store(UpsertCustomerRequest $request, UpsertCustomerAction $action)
    {
        $dto = UpsertCustomerDTO::fromRequest($request);
        $action->execute($dto);

        // Mantenemos el nombre de ruta 'admin.users.index' para no romper tu frontend
        return redirect()->route('admin.users.index')
            ->with('message', 'Cliente registrado exitosamente.');
    }

    /**
     * Carga de Datos para Edición
     */
    public function edit(string $id, GetCustomerForEditAction $action)
    {
        $data = $action->execute($id);
        
        return Inertia::render('Admin/Users/Edit', [
            'user'     => new UserEditResource($data['user']),
            'branches' => $data['branches']
        ]);
    }

    /**
     * Actualización Atómica (El mismo DTO y Action que Store)
     */
    public function update(UpsertCustomerRequest $request, string $id, UpsertCustomerAction $action)
    {
        $dto = UpsertCustomerDTO::fromRequest($request, $id);
        $action->execute($dto);
        
        return redirect()->route('admin.users.index')
            ->with('message', 'Datos del cliente actualizados bajo protocolo seguro.');
    }

    /**
     * Eliminación Controlada (SoftDelete + Purga de Caché)
     */
    public function destroy(string $id, DeleteCustomerAction $action)
    {
        $action->execute($id);

        return redirect()->route('admin.users.index')
            ->with('message', 'Cliente eliminado del silo operativo.');
    }

    /**
     * API Interna: Orquestación del Servicio Geográfico
     */
    public function identifyBranch(Request $request, BranchCoverageService $geoService)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $branchId = $geoService->identifyBranch(
            (float) $request->latitude, 
            (float) $request->longitude
        );

        return response()->json(['branch_id' => $branchId]);
    }
}