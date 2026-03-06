<?php

namespace App\Http\Controllers\Web\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch; // <--- Importado para limpieza
// Requests
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
// DTOs
use App\DTOs\Admin\User\StoreUserDTO;
use App\DTOs\Admin\User\UpdateUserDTO;
// Actions
use App\Actions\Admin\User\GetUsersListAction;
use App\Actions\Admin\User\StoreUserAction;
use App\Actions\Admin\User\GetUserForEditAction;
use App\Actions\Admin\User\UpdateUserAction;
// Resources
use App\Http\Resources\Admin\User\UserResource;
use App\Http\Resources\Admin\User\UserEditResource; 
use App\Services\Geo\BranchCoverageService;

class UserController extends Controller
{
    /**
     * Listado de Clientes (Silo Único)
     */
    public function index(Request $request, GetUsersListAction $action)
    {
        $data = $action->execute($request->only(['search', 'branch_id']));
        
        return Inertia::render('Admin/Users/Index', [
            'users'    => UserResource::collection($data['users']),
            'branches' => $data['branches'],
            'filters'  => $data['filters']
        ]);
    }

    /**
     * Formulario de Nuevo Cliente (Strict Customer)
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'branches' => Branch::all(['id', 'name'])
        ]);
    }

    /**
     * Registro Atómico de Cliente (DTO + Action)
     */
    public function store(StoreUserRequest $request, StoreUserAction $action)
    {
        $dto = StoreUserDTO::fromRequest($request);
        $action->execute($dto);

        return redirect()->route('admin.users.index')
            ->with('message', 'Cliente registrado exitosamente.');
    }

    /**
     * Carga de Datos para Edición (Resource Mapped)
     */
    public function edit(string $id, GetUserForEditAction $action)
    {
        $data = $action->execute($id);
        
        return Inertia::render('Admin/Users/Edit', [
            'user'     => new UserEditResource($data['user']),
            'branches' => $data['branches']
            // PURGADO: Se eliminaron 'roles' y 'type' por irrelevancia en Silo Customer
        ]);
    }

    /**
     * Actualización Atómica (DTO + Action)
     */
    public function update(UpdateUserRequest $request, string $id, UpdateUserAction $action)
    {
        $dto = UpdateUserDTO::fromRequest($request);
        $action->execute($id, $dto);
        
        return redirect()->route('admin.users.index')
            ->with('message', 'Datos del cliente actualizados.');
    }
    


    public function identifyBranch(Request $request, BranchCoverageService $geoService)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $branchId = $geoService->identifyBranch(
            (float) $request->latitude, 
            (float) $request->longitude
        );

        return response()->json(['branch_id' => $branchId]);
    }
}