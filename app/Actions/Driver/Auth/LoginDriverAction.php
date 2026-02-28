<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\LoginDriverData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // 1. Aislamiento de Acceso: Autenticación estricta en el guard 'driver'
        if (! Auth::guard('driver')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // 2. Seguridad Zero-Trust: Política de Sesión Única
        $this->revokeOtherSessions();
    }

    /**
     * Destruye matemáticamente cualquier otra sesión activa para este UUID.
     */
    private function revokeOtherSessions(): void
    {
        $driverId = Auth::guard('driver')->id();
        $currentSessionId = session()->getId();

        DB::table('sessions')
            ->where('user_id', $driverId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}