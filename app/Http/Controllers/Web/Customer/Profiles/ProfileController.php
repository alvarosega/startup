<?php

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\UpdateProfileRequest;
use App\Actions\Customer\Profiles\UpdatePersonalDataAction;
use App\DTOs\Customer\Profiles\ProfileUpdateData;
use App\Http\Resources\Customer\Auth\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index(): Response
    {
        $user = Auth::guard('customer')->user();
        $user->load('profile');
    
        $availableAvatars = collect(range(1, 8))->map(fn($i) => [
            'id' => "avatar_{$i}.png",
            'url' => asset("assets/avatars/avatar_{$i}.png")
        ]);
    
        return Inertia::render('Customer/Profiles/PersonalInfoPage', [
            // CORRECCIÓN: resolve() elimina el envoltorio 'data'
            'user' => (new CustomerResource($user))->resolve(), 
            'availableAvatars' => $availableAvatars,
            'config' => [
                'identity_locked' => true 
            ]
        ]);
    }

    /**
     * VISTA: Mis Direcciones
     * Página dedicada a la gestión logística del cliente.
     */
    public function addresses(): Response
    {
        $user = Auth::guard('customer')->user();
        $user->load('addresses');

        return Inertia::render('Customer/Profiles/AddressesPage', [
            'addresses' => $user->addresses
        ]);
    }

    /**
     * VISTA: Seguridad
     * Gestión de credenciales y acceso.
     */
    public function security(): Response
    {
        return Inertia::render('Customer/Profiles/SecurityPage');
    }

    public function update(UpdateProfileRequest $request, UpdatePersonalDataAction $action): RedirectResponse
    {
        // El DTO captura birth_date y gender del Request Validado
        $action->execute(
            Auth::guard('customer')->user(),
            ProfileUpdateData::fromRequest($request)
        );
    
        return back()->with('success', 'Perfil actualizado.');
    }

    /**
     * ACCIÓN: Cambiar Icono de Perfil
     * Actualiza el avatar_source basándose en los 8 iconos predefinidos.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar_source' => ['required', 'string', 'regex:/^avatar_[1-8]\.png$/']
        ]);

        $user = Auth::guard('customer')->user();
        
        $user->profile->update([
            'avatar_type'   => 'icon',
            'avatar_source' => $request->avatar_source
        ]);

        return back()->with('success', 'Avatar actualizado.');
    }
}