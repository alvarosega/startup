<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin; 
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Importación vital
use App\Http\Requests\Admin\User\StoreUserRequest;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role_id', 'branch_id']);
        $currentUser = auth()->user();

        // 1. QUERY STAFF
        $adminsQuery = DB::table('admins')
            ->select([
                'admins.id', 'admins.first_name', 'admins.last_name', 'admins.email', 'admins.phone',
                'admins.branch_id', 'admins.is_active', 'admins.created_at',
                'admins.last_seen_at', 'admins.last_login_at',
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
                'customers.last_seen_at', 'customers.last_login_at',
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
                'drivers.last_seen_at', 'drivers.last_login_at',
                DB::raw("'Logística Global' as branch_name"),
                DB::raw("'driver' as role_key"), DB::raw("'Conductor' as role_label"),
                DB::raw("'driver' as type")
            ])
            ->leftJoin('driver_details', 'drivers.id', '=', 'driver_details.driver_id');

        if ($currentUser->hasRole('branch_admin')) {
            $binId = $currentUser->getRawOriginal('branch_id');
            $adminsQuery->where('admins.branch_id', $binId);
            $customersQuery->where('customers.branch_id', $binId);
            $driversQuery->whereRaw('1 = 0');
        }

        $users = $adminsQuery->unionAll($customersQuery)->unionAll($driversQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function($u) {
                $lastSeen = $u->last_seen_at ? Carbon::parse($u->last_seen_at) : null;
                return [
                    'id' => bin2hex($u->id),
                    'name' => trim("$u->first_name $u->last_name"),
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'role_label' => $u->role_label,
                    'type' => $u->type,
                    'branch' => $u->branch_name,
                    'is_active' => (bool)$u->is_active,
                    'status' => [
                        'online' => $lastSeen && $lastSeen->diffInMinutes(now()) < 5,
                        'last_seen' => $lastSeen ? $lastSeen->diffForHumans() : 'Nunca',
                        'last_login' => $u->last_login_at ? Carbon::parse($u->last_login_at)->format('d/m/Y H:i') : 'N/A'
                    ]
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'users'    => $users,
            'roles'    => Role::whereIn('guard_name', ['super_admin', 'customer', 'driver'])->get(),
            'branches' => Branch::all()->map(fn($b) => [
                'id' => bin2hex($b->getRawOriginal('id')), 
                'name' => $b->name
            ]),
            'filters'  => $filters 
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::whereIn('guard_name', ['super_admin', 'customer', 'driver'])
                ->where('name', '!=', 'super_admin')
                ->get()
                ->map(fn($role) => [
                    'id'   => $role->id,
                    'name' => $role->name,
                    'description' => match($role->name) {
                        'customer' => 'Usuario final que realiza pedidos.',
                        'driver'   => 'Encargado de logística.',
                        default    => 'Miembro del staff administrativo.',
                    }
                ]),
            'branches' => Branch::all()->map(fn($b) => [
                'id'   => bin2hex($b->getRawOriginal('id')),
                'name' => $b->name
            ]),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        Log::info('[UserStore] Inicio de petición', ['email' => $request->email, 'role_id' => $request->role_id]);

        $role = Role::findOrFail($request->role_id);
        Log::info('[UserStore] Rol detectado', ['name' => $role->name, 'guard' => $role->guard_name]);

        try {
            return DB::transaction(function () use ($request, $role) {
                
                if ($role->name === 'customer') {
                    Log::info('[UserStore] Procesando Silo Customer');
                    $customer = Customer::create([
                        'email'     => $request->email,
                        'phone'     => $request->phone,
                        'password'  => $request->password,
                        'branch_id' => $request->branch_id ? hex2bin($request->branch_id) : null,
                        'is_active' => true,
                    ]);

                    $customer->profile()->create([
                        'first_name' => $request->first_name,
                        'last_name'  => $request->last_name,
                        'latitude'   => $request->latitude,
                        'longitude'  => $request->longitude,
                        'address'    => $request->address,
                    ]);

                    $customer->assignRole($role);
                    $hexId = bin2hex($customer->getRawOriginal('id'));
                    Log::info('[UserStore] Customer creado con éxito', ['id' => $hexId]);
                    $msg = 'Cliente creado.';

                } elseif ($role->name === 'driver') {
                    Log::info('[UserStore] Procesando Silo Driver');
                    $driver = Driver::create([
                        'email'    => $request->email,
                        'phone'    => $request->phone,
                        'password' => $request->password,
                        'is_active' => true,
                    ]);

                    $driver->details()->create([
                        'first_name' => $request->first_name,
                        'last_name'  => $request->last_name,
                    ]);

                    $driver->assignRole($role);
                    Log::info('[UserStore] Driver creado con éxito');
                    $msg = 'Conductor creado.';

                } else {
                    Log::info('[UserStore] Procesando Silo Staff');
                    $admin = Admin::create([
                        'first_name' => $request->first_name,
                        'last_name'  => $request->last_name,
                        'email'      => $request->email,
                        'phone'      => $request->phone,
                        'password'   => $request->password,
                        'branch_id'  => $request->branch_id ? hex2bin($request->branch_id) : null,
                        'is_active'  => true,
                    ]);

                    $admin->assignRole($role);
                    Log::info('[UserStore] Staff creado con éxito');
                    $msg = 'Staff creado.';
                }

                return redirect()->route('admin.users.index')->with('message', $msg);
            });
        } catch (\Exception $e) {
            Log::error('[UserStore] ERROR CRÍTICO', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
            
            // Relanzamos para que Inertia capture el error, 
            // pero el log ya guardó la causa real antes del colapso UTF-8.
            throw $e; 
        }
    }
}