<?php

declare(strict_types=1);

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\LoginDriverData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginDriverAction
{
    public function execute(LoginDriverData $data): void
    {
        $credentials = [
            'phone'    => $data->phone,
            'password' => $data->password,
        ];

        // RECTIFICACIÓN: Se añade el segundo parámetro para la cookie 'remember'
        if (! Auth::guard('driver')->attempt($credentials, $data->remember)) {
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $driver = Auth::guard('driver')->user();

        if ($driver->status === 'pending') {
            Auth::guard('driver')->logout();
            throw ValidationException::withMessages([
                'phone' => 'Tu cuenta está en revisión. Te notificaremos cuando sea aprobada.',
            ]);
        }

        if ($driver->status === 'suspended') {
            Auth::guard('driver')->logout();
            throw ValidationException::withMessages([
                'phone' => 'Tu cuenta ha sido suspendida.',
            ]);
        }

        $driver->update([
            'last_login_at' => now(),
            'is_online'     => true
        ]);
    }
}