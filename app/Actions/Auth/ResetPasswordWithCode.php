<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\ResetPasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPasswordWithCode
{
    public function execute(ResetPasswordData $data): User
    {
        // 1. Recuperar código de caché
        $cachedCode = Cache::get('password_reset_' . $data->email);

        // 2. Validar Código
        if (!$cachedCode || (string)$cachedCode !== (string)$data->code) {
            throw ValidationException::withMessages([
                'code' => ['El código de verificación es inválido o ha expirado.']
            ]);
        }

        // 3. Obtener Usuario
        $user = User::where('email', $data->email)->firstOrFail();

        // 4. Actualizar Contraseña
        $user->forceFill([
            'password' => Hash::make($data->password)
        ])->save();

        // 5. Limpiar Caché (Evitar reuso del código)
        Cache::forget('password_reset_' . $data->email);

        return $user;
    }
}