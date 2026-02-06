<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Identity\UpdateProfileRequest; // Asumo que este Request ya lo tienes
use App\DTOs\Identity\ProfileData; // Asumo que este DTO ya lo tienes
use App\Actions\Identity\UpdateProfile; // Asumo que esta Action ya la tienes
use App\Models\Branch;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['profile', 'addresses', 'driverProfile']);

        // Traemos las sucursales para el Modal de Direcciones
        $activeBranches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon', 'latitude', 'longitude')
            ->get();

        return Inertia::render('Profile/Index', [
            'user' => $user,
            'addresses_count' => $user->addresses->count(),
            'is_driver' => $user->hasRole('driver'),
            'activeBranches' => $activeBranches, // <--- INYECTAR AQUÍ
        ]);
    }

    public function edit()
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user()->load('profile'),
            'status' => session('status'),
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $action)
    {
        try {
            $data = ProfileData::fromRequest($request);
            
            // La Action debe contener la lógica de "Si está verificado, no editar ciertos campos"
            $action->execute($request->user(), $data);

            return to_route('profile.index')->with('success', 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'No se pudieron guardar los cambios. Verifique los datos.']);
        }
    }

    // Si manejas el avatar en este controlador:
    public function updateAvatar(\Illuminate\Http\Request $request) 
    {
        $request->validate([
            'avatar' => 'required|image|max:3072' // (3MB max)
        ]);
         
         // Lógica simple de avatar (o muévela a una Action si es compleja)
         $user = $request->user();
         $path = $request->file('avatar')->store('avatars', 'public');
         
         $user->update([
             'avatar_type' => 'storage',
             'avatar_source' => $path
         ]);

         return back()->with('success', 'Avatar actualizado.');
    }
}