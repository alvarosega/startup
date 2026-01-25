<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// Arquitectura Clean
use App\DTOs\Identity\ProfileData;
use App\Http\Requests\Identity\UpdateProfileRequest;
use App\Actions\Identity\UpdateProfile;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['profile', 'addresses']);

        return Inertia::render('Profile/Index', [
            'user' => $user,
            'addresses_count' => $user->addresses->count(),
            'is_driver' => $user->hasRole('driver'),
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
            $action->execute($request->user(), $data);

            return to_route('profile.index')->with('success', 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al guardar los datos.']);
        }
    }
    
    // ... (Método updateAvatar sigue igual si ya lo tienes)
}