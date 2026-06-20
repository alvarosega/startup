<?php

namespace App\Actions\Admin\Auth;

use App\DTOs\Admin\Auth\LoginAdminData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Admin;

class LoginAdminAction
{
    public function execute(LoginAdminData $data): bool
    {
        // El guard intenta autenticar.
        // NOTA: No filtramos 'is_active' aquí para poder dar un error específico después.
        if (!Auth::guard('super_admin')->attempt([
            'email'    => $data->email,
            'password' => $data->password
        ], $data->remember)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        // Una vez autenticado, verificamos integridad de estado
        $admin = Auth::guard('super_admin')->user();

        if (!$admin->is_active) {
            Auth::guard('super_admin')->logout(); // Expulsión inmediata
            throw ValidationException::withMessages(['email' => 'Acceso denegado: Cuenta administrativa inactiva.']);
        }

        return true;
    }
}