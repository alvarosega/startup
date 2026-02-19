<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Admin, Customer, Driver, Branch};
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\{DB, Log};
use App\Http\Requests\Admin\User\StoreUserRequest;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role_id', 'branch_id']);
        
        // 1. QUERY STAFF (ADMINS)
        $adminsQuery = DB::table('admins')
            ->select([
                'admins.id', 'admins.first_name', 'admins.last_name', 'admins.email', 'admins.phone',
                'admins.branch_id', 'admins.is_active', 'admins.created_at',
                'branches.name as branch_name', 
                'roles.name as role_key', 'roles.display_name as role_label',
                DB::raw("'admin' as type")
            ])
            ->leftJoin('branches', 'admins.branch_id', '=', 'branches.id')
            ->leftJoin('model_has_roles', function($join) {
                $join->on('admins.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', Admin::class);
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id');

        // 2. QUERY CLIENTES
        $customersQuery = DB::table('customers')
            ->select([
                'customers.id', 'customer_profiles.first_name', 'customer_profiles.last_name', 'customers.email', 'customers.phone',
                'customers.branch_id', 'customers.is_active', 'customers.created_at',
                'branches.name as branch_name',
                DB::raw("'customer' as role_key"), DB::raw("'Cliente' as role_label"),
                DB::raw("'customer' as type")
            ])
            ->leftJoin('customer_profiles', 'customers.id', '=', 'customer_profiles.customer_id')
            ->leftJoin('branches', 'customers.branch_id', '=', 'branches.id');

        // 3. QUERY CONDUCTORES
        $driversQuery = DB::table('drivers')
            ->select([
                'drivers.id', 'driver_details.first_name', 'driver_details.last_name', 'drivers.email', 'drivers.phone',
                DB::raw("NULL as branch_id"), DB::raw("1 as is_active"), 'drivers.created_at',
                DB::raw("'Logística Global' as branch_name"),
                DB::raw("'driver' as role_key"), DB::raw("'Conductor' as role_label"),
                DB::raw("'driver' as type")
            ])
            ->leftJoin('driver_details', 'drivers.id', '=', 'driver_details.driver_id');

        // APLICACIÓN DE FILTROS GLOBALES (Antes del Union para rendimiento)
        if ($request->search) {
            $s = "%{$request->search}%";
            foreach ([$adminsQuery, $customersQuery, $driversQuery] as $q) {
                $q->where(function($query) use ($s) {
                    $query->where('email', 'like', $s)
                          ->orWhere('phone', 'like', $s)
                          ->orWhere('first_name', 'like', $s)
                          ->orWhere('last_name', 'like', $s);
                });
            }
        }

        // UNIÓN Y PAGINACIÓN
        $users = $adminsQuery->unionAll($customersQuery)->unionAll($driversQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($u) => [
                'id'         => $u->id, // YA ES STRING UUID
                'name'       => trim("$u->first_name $u->last_name"),
                'email'      => $u->email,
                'phone'      => $u->phone,
                'role_key'   => $u->role_key,
                'role_label' => $u->role_label,
                'type'       => $u->type,
                'branch'     => $u->branch_name ?? 'Sin Sucursal',
                'is_active'  => (bool)$u->is_active,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users'    => $users,
            'roles'    => Role::whereIn('guard_name', ['super_admin', 'customer', 'driver'])->get(),
            'branches' => Branch::all(['id', 'name']), // Directo, ya son strings
            'filters'  => $filters 
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $role = Role::findOrFail($request->role_id);
            
            // ELIMINADO: hex2bin. Usamos el ID de la sucursal tal cual viene (String).
            $data = $request->validated();
            $branchId = $data['branch_id'] ?? null;

            if ($role->name === 'customer') {
                $user = Customer::create([...$data, 'branch_id' => $branchId]);
                $user->profile()->create($data);
            } elseif ($role->name === 'driver') {
                $user = Driver::create($data);
                $user->details()->create($data);
            } else {
                $user = Admin::create([...$data, 'branch_id' => $branchId]);
            }

            $user->assignRole($role);

            return redirect()->route('admin.users.index')->with('message', 'Usuario creado correctamente.');
        });
    }
}