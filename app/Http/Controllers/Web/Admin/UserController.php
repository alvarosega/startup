<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin; 
use App\Models\Customer; // Importar Customer
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\DTOs\Customer\Auth\RegisterCustomerData;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role_id', 'branch_id']);
        $currentUser = auth()->user();

        // 1. QUERY STAFF (Admins)
        $adminsQuery = DB::table('admins')
            ->select([
                'admins.id', 'admins.first_name', 'admins.last_name', 'admins.email', 'admins.phone',
                'admins.branch_id', 'admins.is_active', 'admins.created_at',
                'admins.role_level', // Columna 9
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

        // 2. QUERY CLIENTES (Customers)
        $customersQuery = DB::table('customers')
            ->select([
                'customers.id', 'customer_profiles.first_name', 'customer_profiles.last_name', 'customers.email', 'customers.phone',
                'customers.branch_id', 'customers.is_active', 'customers.created_at',
                DB::raw("NULL as role_level"), // Columna 9 (Alineación)
                'branches.name as branch_name',
                DB::raw("'customer' as role_key"), DB::raw("'Cliente' as role_label"),
                DB::raw("'customer' as type")
            ])
            ->leftJoin('customer_profiles', 'customers.id', '=', 'customer_profiles.customer_id')
            ->leftJoin('branches', 'customers.branch_id', '=', 'branches.id');

        // 3. QUERY CONDUCTORES (Drivers)
        $driversQuery = DB::table('drivers')
            ->select([
                'drivers.id', 'driver_details.first_name', 'driver_details.last_name', 'drivers.email', 'drivers.phone',
                DB::raw("NULL as branch_id"), DB::raw("1 as is_active"), 'drivers.created_at',
                DB::raw("NULL as role_level"), // Columna 9 (Alineación)
                DB::raw("'Logística Global' as branch_name"),
                DB::raw("'driver' as role_key"), DB::raw("'Conductor' as role_label"),
                DB::raw("'driver' as type")
            ])
            ->leftJoin('driver_details', 'drivers.id', '=', 'driver_details.driver_id');

        // --- FILTROS ---
        if ($currentUser->hasRole('branch_admin')) {
            $binId = $currentUser->getRawOriginal('branch_id');
            $adminsQuery->where('admins.branch_id', $binId);
            $customersQuery->where('customers.branch_id', $binId);
            $driversQuery->whereRaw('1 = 0');
        }

        if ($search = $filters['search'] ?? null) {
            $applyS = function($q, $table, $profTable = null) use ($search) {
                $q->where(function($sub) use ($search, $table, $profTable) {
                    $sub->where("$table.email", 'like', "%$search%")->orWhere("$table.phone", 'like', "%$search%");
                    $t = $profTable ?? $table;
                    $sub->orWhere("$t.first_name", 'like', "%$search%")->orWhere("$t.last_name", 'like', "%$search%");
                });
            };
            $applyS($adminsQuery, 'admins');
            $applyS($customersQuery, 'customers', 'customer_profiles');
            $applyS($driversQuery, 'drivers', 'driver_details');
        }

        // --- UNIÓN Y RESPUESTA ---
        $users = $adminsQuery->unionAll($customersQuery)->unionAll($driversQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($u) => [
                'id' => bin2hex($u->id),
                'name' => trim("$u->first_name $u->last_name"),
                'email' => $u->email,
                'phone' => $u->phone,
                'role_key' => ($u->role_level === 'super_admin') ? 'super_admin' : ($u->role_key ?? 'staff'),
                'role_label' => ($u->role_level === 'super_admin') ? 'Super Admin' : ($u->role_label ?? 'Personal'),
                'branch' => $u->branch_name,
                'is_active' => (bool)$u->is_active,
                'type' => $u->type
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::where('name', '!=', 'super_admin')->get(['id', 'name', 'display_name'])->map(fn($r) => [
                'id' => (string)$r->id, 'name' => $r->name, 'display_name' => $r->display_name
            ]),
            'branches' => Branch::where('is_active', true)->get()->map(fn($b) => [
                'id' => bin2hex($b->getRawOriginal('id') ?? $b->id), 'name' => $b->name
            ]),
        ]);
    }
    public function store(StoreUserRequest $request)
    {
        $role = Role::findOrFail($request->role_id);

        if ($role->name === 'customer') {
            $dto = new RegisterCustomerData(
                phone: $request->phone, email: $request->email, password: $request->password,
                address: $request->address, alias: 'Dirección Principal', details: 'Alta Admin',
                latitude: (float)$request->latitude, longitude: (float)$request->longitude,
                branch_id: $request->branch_id, avatar_type: 'icon', avatar_source: 'avatar_1.svg', avatar_file: null
            );
            $customer = app(RegisterCustomerAction::class)->execute($dto);
            $customer->profile()->update(['first_name' => $request->first_name, 'last_name' => $request->last_name]);
            return redirect()->route('admin.users.index')->with('message', 'Cliente creado.');
        }

        $binBranch = ($request->branch_id && ctype_xdigit($request->branch_id)) ? hex2bin($request->branch_id) : null;
        $admin = Admin::create([
            'first_name' => $request->first_name, 'last_name' => $request->last_name,
            'email' => $request->email, 'phone' => $request->phone,
            'password' => Hash::make($request->password), 'branch_id' => $binBranch,
            'is_active' => true, 'role_level' => 'moderator'
        ]);
        $admin->assignRole($role);
        return redirect()->route('admin.users.index')->with('message', 'Staff creado.');
    }
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::where('name', '!=', 'super_admin')->get(),
            'branches' => Branch::where('is_active', true)->get()->map(fn($b) => [
                'id' => bin2hex($b->getRawOriginal('id') ?? $b->id),
                'name' => $b->name, 'coverage_polygon' => $b->coverage_polygon
            ])
        ]);
    }

    
    public function destroy($id)
    {
        $binId = hex2bin($id);
        if ($admin = Admin::where('id', $binId)->first()) {
            if (auth()->id() === $admin->id) return back()->with('error', 'No puedes eliminarte.');
            $admin->delete();
            return back()->with('message', 'Usuario eliminado.');
        }
        if ($customer = Customer::where('id', $binId)->first()) {
            $customer->delete();
            return back()->with('message', 'Cliente eliminado.');
        }
        return back()->with('error', 'No encontrado.');
    }
}