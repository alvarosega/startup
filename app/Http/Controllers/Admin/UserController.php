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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id', // Nullable permitido
        ]);

        DB::transaction(function () use ($validated) {
            $phone = str_replace(' ', '', $validated['phone']);
            if (!str_starts_with($phone, '+')) $phone = '+591' . $phone;

            $user = User::create([
                'phone' => $phone,
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'branch_id' => $validated['branch_id'],
                'is_active' => true,
                'avatar_type' => 'icon',
                'avatar_source' => 'avatar_1.svg'
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'is_identity_verified' => true
            ]);

            $user->roles()->attach($validated['role_id']);
        });

        return redirect()->route('admin.users.index')->with('message', 'Usuario creado exitosamente.');
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

    public function update(Request $request, User $user)
    {
        if (auth()->user()->hasRole('branch_admin') && $user->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email'],
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id', // Nullable
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($validated, $user) {
            $userData = [
                'branch_id' => $validated['branch_id'], // Puede ser null
                'is_active' => $validated['is_active'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name']
                ]
            );

            // Actualizar Rol (Sincronizar tabla intermedia)
            $user->roles()->sync([$validated['role_id']]);
        });

        return redirect()->route('admin.users.index')->with('message', 'Usuario actualizado correctamente.');
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