<?php

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

        // 1. Autenticación estricta en el guard 'driver'
        if (! Auth::guard('driver')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales proporcionadas no coinciden con nuestros registros o la cuenta no existe.',
            ]);
        }

        // El registro de último login (last_login_at) se puede disparar desde un Listener de Eventos
        // o si prefieres, actualízalo aquí directamente:
        $driver = Auth::guard('driver')->user();
        $driver->update(['last_login_at' => now()]);
    }
}