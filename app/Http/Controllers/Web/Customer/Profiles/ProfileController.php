<?php

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\UpdateProfileRequest;
use App\Actions\Customer\Profiles\UpdatePersonalDataAction;
use App\Actions\Customer\Profiles\UpdateAvatarAction;
use App\DTOs\Customer\Profiles\ProfileUpdateData;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * VISTA: Información Personal
     */
    public function index()
    {
        $user = Auth::guard('customer')->user();
        $user->load('profile');

        return Inertia::render('Customer/Profiles/PersonalInfoPage', [
            'user' => $user
        ]);
    }

    /**
     * VISTA: Seguridad
     */
    public function security()
    {
        return Inertia::render('Customer/Profiles/SecurityPage');
    }

    /**
     * ACCIÓN: Actualizar Datos Personales
     */
    public function update(UpdateProfileRequest $request, UpdatePersonalDataAction $action)
    {
        $action->execute(
            Auth::guard('customer')->user(),
            ProfileUpdateData::fromRequest($request)
        );

        return back()->with('success', 'Perfil actualizado.');
    }

    /**
     * ACCIÓN: Actualizar Avatar (Icono o Custom)
     */
    public function updateAvatar(\Illuminate\Http\Request $request, UpdateAvatarAction $action)
    {
        $action->execute(
            Auth::guard('customer')->user(),
            $request->only(['avatar_type', 'avatar_source', 'avatar_file'])
        );

        return back()->with('success', 'Avatar actualizado.');
    }
}