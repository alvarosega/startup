<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\DTOs\Admin\Auth\LoginAdminData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LoginAdminAction
{
    /**
     * Ejecuta la autenticación y las validaciones de estado y roles de seguridad.
     * * @throws ValidationException
     */
    public function execute(LoginAdminData $data): bool
    {
        // Intento de autenticación bajo el guard administrativo 'super_admin'
        if (!Auth::guard('super_admin')->attempt([
            'email'    => $data->email,
            'password' => $data->password
        ], $data->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var \App\Models\Users\Admin|null $admin */
        $admin = Auth::guard('super_admin')->user();

        // Control estricto de seguridad: validación de cuenta activa y rol mandatorio
        // Devuelve un error genérico (auth.failed) para evitar la enumeración de cuentas
        if (!$admin || !$admin->is_active || !$admin->hasRole('super_admin')) {
            Auth::guard('super_admin')->logout();
            
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Mutación de la persistencia obligatoria exigida por el caso de éxito
        $admin->update([
            'last_login_at' => Carbon::now(),
            'last_seen_at'  => Carbon::now(),
        ]);

        return true;
    }
}