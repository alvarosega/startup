<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\LoginDriverData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginDriverAction
{
    /**
     * @throws ValidationException
     */
    public function execute(LoginDriverData $data): void
    {
        $credentials = [
            'phone' => $data->phone,
            'password' => $data->password,
        ];

        // 1. Autenticación estricta en el guard 'driver'
        if (! Auth::guard('driver')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // 2. Política de Sesión Única (Nativa de Laravel)
        // Invalida todas las sesiones excepto la actual. 
        // Requiere que el middleware AuthenticateSession esté activo.
        Auth::guard('driver')->logoutOtherDevices($data->password);
    }
}