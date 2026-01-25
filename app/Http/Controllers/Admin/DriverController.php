<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\DriverProfile; // <--- Importante
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Resources\DriverProfileResource;


class DriverController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        $drivers = User::role('driver')
            ->with(['profile', 'driverProfile']) // Cargamos ambas relaciones
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhereHas('profile', function ($q2) use ($search) {
                          $q2->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('driverProfile', function ($q3) use ($search) { // Buscar por placa
                          $q3->where('license_plate', 'like', "%{$search}%");
                      });
                });
            })
            // Filtro pendiente (ahora en driverProfile->status o is_verified si lo mantienes ahí)
            // Asumiremos que verificamos status en driverProfile
            ->when(($filters['status'] ?? null) === 'pending', function ($query) {
                $query->whereHas('driverProfile', fn($q) => $q->where('status', 'pending'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($user) => [
                'id' => $user->id,
                'full_name' => $user->profile ? ($user->profile->first_name . ' ' . $user->profile->last_name) : 'Sin Nombre',
                'phone' => $user->phone,
                // Datos del DriverProfile
                'vehicle_type' => $user->driverProfile->vehicle_type ?? 'N/A',
                'license_plate' => $user->driverProfile->license_plate ?? 'N/A',
                'status' => $user->driverProfile->status ?? 'pending',
                'is_verified' => ($user->driverProfile->status ?? '') === 'verified',
                'is_active' => $user->is_active,
                'created_at' => $user->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Drivers/Index', [
            'drivers' => $drivers,
            'filters' => $filters,
            'pending_count' => User::role('driver')->whereHas('driverProfile', fn($q) => $q->where('status', 'pending'))->count()
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
                'is_active' => true,
                'avatar_type' => 'icon',
                'avatar_source' => 'avatar_1.svg'
            ]);

            $user->assignRole('driver');

            // 1. Perfil Humano
            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'is_identity_verified' => true 
            ]);

            // 2. Perfil Conductor
            DriverProfile::create([
                'user_id' => $user->id,
                'license_number' => $validated['license_number'],
                'license_plate' => $validated['license_plate'],
                'vehicle_type' => $validated['vehicle_type'],
                'status' => 'verified' // Si lo crea admin, nace verificado
            ]);
        });

        return redirect()->route('admin.drivers.index')->with('success', 'Conductor registrado exitosamente.');
    }

    public function edit(User $driver)
    {
        $driver->load(['profile', 'driverProfile']); // Cargar ambas relaciones
        
        return Inertia::render('Admin/Drivers/Edit', [
            'driver' => [
                'id' => $driver->id,
                'first_name' => $driver->profile->first_name ?? '',
                'last_name' => $driver->profile->last_name ?? '',
                'phone' => $driver->phone,
                // Datos Driver
                'license_number' => $driver->driverProfile->license_number ?? '',
                'license_plate' => $driver->driverProfile->license_plate ?? '',
                'vehicle_type' => $driver->driverProfile->vehicle_type ?? 'moto',
                
                // Usamos el status del driverProfile para determinar si está verificado
                'is_identity_verified' => ($driver->driverProfile->status ?? '') === 'verified',
                'status' => $driver->driverProfile->status ?? 'pending',
                
                'is_active' => (bool)$driver->is_active,
                
                'profile_docs' => [
                    'ci_front_path' => $driver->driverProfile->ci_front_path ?? null,
                    'license_photo_path' => $driver->driverProfile->license_photo_path ?? null,
                    'vehicle_photo_path' => $driver->driverProfile->vehicle_photo_path ?? null,
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
            'is_identity_verified' => 'boolean', // Checkbox en la UI
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($driver, $validated) {
            $driver->update(['is_active' => $validated['is_active']]);
            
            // Actualizar datos humanos
            $driver->profile()->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
            ]);

            // Actualizar datos conductor
            // Calculamos el estado basado en el checkbox de verificación
            $newStatus = $validated['is_identity_verified'] ? 'verified' : 'pending';

            $driver->driverProfile()->update([
                'license_number' => $validated['license_number'],
                'license_plate' => $validated['license_plate'],
                'vehicle_type' => $validated['vehicle_type'],
                'status' => $newStatus
            ]);
        });

        return redirect()->route('admin.drivers.index')->with('success', 'Perfil de conductor actualizado.');
    }
    public function editProfile()
    {
        $user = Auth::user();
        $user->load('driverProfile');

        return Inertia::render('Driver/EditProfile', [
            // Reutilizamos el Resource que ya creamos, es perfecto para esto
            'driver' => $user->driverProfile 
                ? new DriverProfileResource($user->driverProfile) 
                : null
        ]);
    }
    public function updateProfile(UpdateDriverProfileRequest $request, UpdateDriverProfile $action)
    {
        // ... (código que generamos en el turno anterior)
    }
}