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
        if (!Auth::guard('super_admin')->attempt([
            'email'    => $data->email,
            'password' => $data->password
        ])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var \App\Models\Users\Admin|null $admin */
        $admin = Auth::guard('super_admin')->user();

        if (!$admin || !$admin->is_active || !$admin->hasRole('super_admin')) {
            Auth::guard('super_admin')->logout();
            
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $admin->update([
            'last_login_at' => Carbon::now(),
            'last_seen_at'  => Carbon::now(),
        ]);

        return true;
    }
}