<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        // Filtramos SOLO usuarios con rol 'driver'
        $drivers = User::role('driver')
            ->with('profile')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhereHas('profile', function ($q2) use ($search) {
                          $q2->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('license_plate', 'like', "%{$search}%");
                      });
                });
            })
            // Filtro especial: Pendientes de verificación
            ->when(($filters['status'] ?? null) === 'pending', function ($query) {
                $query->whereHas('profile', fn($q) => $q->where('is_identity_verified', false));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($user) => [
                'id' => $user->id,
                'full_name' => $user->profile ? ($user->profile->first_name . ' ' . $user->profile->last_name) : 'Sin Nombre',
                'phone' => $user->phone,
                'vehicle_type' => $user->profile->vehicle_type ?? 'N/A',
                'license_plate' => $user->profile->license_plate ?? 'N/A',
                'is_verified' => (bool)$user->profile->is_identity_verified,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Drivers/Index', [
            'drivers' => $drivers,
            'filters' => $filters,
            // Contador para las pestañas
            'pending_count' => User::role('driver')->whereHas('profile', fn($q) => $q->where('is_identity_verified', false))->count()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Drivers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:6',
            'license_number' => 'required|string',
            'license_plate' => 'required|string',
            'vehicle_type' => 'required|in:moto,car,truck',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'is_active' => true, // Si lo crea el admin, nace activo
                'avatar_type' => 'icon',
                'avatar_source' => 'avatar_1.svg'
            ]);

            $user->assignRole('driver');

            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'license_number' => $validated['license_number'],
                'license_plate' => $validated['license_plate'],
                'vehicle_type' => $validated['vehicle_type'],
                'is_identity_verified' => true // Si lo crea el admin manual, nace verificado
            ]);
        });

        return redirect()->route('admin.drivers.index')->with('success', 'Conductor registrado exitosamente.');
    }

    public function edit(User $driver)
    {
        $driver->load('profile');
        
        return Inertia::render('Admin/Drivers/Edit', [
            'driver' => [
                'id' => $driver->id,
                'first_name' => $driver->profile->first_name ?? '',
                'last_name' => $driver->profile->last_name ?? '',
                'phone' => $driver->phone,
                'license_number' => $driver->profile->license_number ?? '',
                'license_plate' => $driver->profile->license_plate ?? '',
                'vehicle_type' => $driver->profile->vehicle_type ?? 'moto',
                'is_identity_verified' => (bool)($driver->profile->is_identity_verified ?? false),
                'is_active' => (bool)$driver->is_active,
                
                // [IMPORTANTE] ESTO ES LO QUE NECESITA LA NUEVA VISTA
                'profile_docs' => [
                    'ci_front_path' => $driver->profile->ci_front_path,
                    'license_photo_path' => $driver->profile->license_photo_path,
                    'vehicle_photo_path' => $driver->profile->vehicle_photo_path,
                ]
            ]
        ]);
    }

    public function update(Request $request, User $driver)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'license_number' => 'required',
            'license_plate' => 'required',
            'vehicle_type' => 'required',
            'is_identity_verified' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $driver->update(['is_active' => $validated['is_active']]);
        
        $driver->profile()->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'license_number' => $validated['license_number'],
            'license_plate' => $validated['license_plate'],
            'vehicle_type' => $validated['vehicle_type'],
            'is_identity_verified' => $validated['is_identity_verified'],
        ]);

        return redirect()->route('admin.drivers.index')->with('success', 'Perfil de conductor actualizado.');
    }
}