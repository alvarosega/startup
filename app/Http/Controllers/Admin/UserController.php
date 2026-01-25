<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Admin\User\StoreUserRequest;  // <--- NUEVO
use App\DTOs\User\UserData;
use App\Actions\User\CreateUser; // <--- ESTA FALTA
use App\Actions\User\UpdateUser;
use App\Http\Requests\Admin\User\UpdateUserRequest;


class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role_id', 'branch_id']);

        $usersQuery = User::with(['roles', 'branch', 'profile'])
            ->when(auth()->user()->hasRole('branch_admin'), function ($q) {
                $q->where('branch_id', auth()->user()->branch_id);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhereHas('profile', function ($q2) use ($search) {
                          $q2->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      });
                });
            })
            // CORRECCIÓN CRÍTICA: Especificar tabla roles para evitar "Column 'id' ambiguous"
            ->when($filters['role_id'] ?? null, function ($query, $roleId) {
                $query->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('roles.id', $roleId); 
                });
            })
            ->when($filters['branch_id'] ?? null, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->orderBy('id', 'desc');

        $users = $usersQuery->paginate(10)->withQueryString()
            ->through(function ($user) {
                // Nombre del rol seguro
                $roleName = $user->roles->first()?->display_name ?? $user->roles->first()?->name ?? 'Sin Rol';
                $roleKey = $user->roles->first()?->name ?? '';

                return [
                    'id' => $user->id,
                    'full_name' => $user->profile ? ($user->profile->first_name . ' ' . $user->profile->last_name) : 'Sin Perfil',
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $roleName,
                    'role_key' => $roleKey, // Para agrupar en el frontend
                    'branch' => $user->branch?->name,
                    'is_active' => $user->is_active,

                    'is_verified' => $user->profile?->is_identity_verified ?? false,
                    'vehicle' => $user->profile?->vehicle_type, // 'moto', 'car', etc.
                    'plate' => $user->profile?->license_plate,
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::where('name', '!=', 'super_admin')->get(['id', 'name', 'display_name']),
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $isSuper = $user->hasRole('super_admin');

        $rolesQuery = Role::query();
        if (!$isSuper) {
            $rolesQuery->where('name', '!=', 'super_admin')
                       ->where('name', '!=', 'branch_admin');
        }

        return Inertia::render('Admin/Users/Create', [
            'roles' => $rolesQuery->get(),
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
        ]);
    }

    public function store(StoreUserRequest $request, CreateUser $action)
    {
        // 1. DTO (Se llena con datos ya validados)
        $data = UserData::fromRequest($request);

        // 2. Acción
        $action->execute($data);

        return redirect()->route('admin.users.index')
            ->with('message', 'Usuario creado exitosamente.');
    }

    // --- AQUÍ ESTABA EL ERROR DEL 500 ---
    // Asegúrate de que este método exista y reciba el modelo User correctamente
    public function edit(User $user)
    {
        // Seguridad: Admin Sucursal no edita a otros branches
        if (auth()->user()->hasRole('branch_admin') && $user->branch_id !== auth()->user()->branch_id) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }
        
        $user->load(['profile', 'roles']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->profile->first_name ?? '',
                'last_name' => $user->profile->last_name ?? '',
                'email' => $user->email,
                'phone' => $user->phone,
                'role_id' => $user->roles->first()?->id,
                'branch_id' => $user->branch_id,
                'is_active' => (bool)$user->is_active
            ],
            'roles' => Role::where('name', '!=', 'super_admin')->get(),
            'branches' => Branch::where('is_active', true)->get()
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $action)
    {
        // Seguridad: Branch Admin no toca otros branches
        if (auth()->user()->hasRole('branch_admin') && $user->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

        // 1. DTO
        $data = UserData::fromRequest($request);

        // 2. Acción
        $action->execute($user, $data);

        return redirect()->route('admin.users.index')
            ->with('message', 'Usuario actualizado correctamente.');
    }
    public function destroy(User $user)
    {
        if (auth()->user()->hasRole('branch_admin') && $user->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta.']);
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('message', 'Usuario eliminado.');
    }
}