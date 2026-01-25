<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Actions & DTOs
use App\Actions\Profile\UpdateUserProfile;
use App\Actions\Profile\UpdateUserAvatar;
use App\DTOs\Profile\UpdateProfileData;
use App\DTOs\Profile\UpdateAvatarData;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    public function index()
    {
        // CAMBIO: Usamos fresh() para forzar la recarga de datos de la BD
        // y cargamos explícitamente las relaciones 'profile' y 'driverProfile'
        $user = Auth::user()->fresh(['profile', 'driverProfile']);
        
        $profileData = (new ProfileResource($user))->resolve();

        return Inertia::render('Profile/Index', [
            'user' => $profileData,
            'addresses_count' => $user->addresses()->count(),
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        return Inertia::render('Profile/Edit', [
            'user' => (new ProfileResource($user))->resolve(),
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateUserProfile $action)
    {
        $data = UpdateProfileData::fromRequest($request);
        $action->execute($request->user(), $data);

        return back()->with('message', 'Perfil actualizado correctamente.');
    }

    public function updateAvatar(UpdateAvatarRequest $request, UpdateUserAvatar $action)
    {
        $data = UpdateAvatarData::fromRequest($request);
        $action->execute($request->user(), $data);

        return back()->with('message', 'Avatar actualizado.');
    }
}