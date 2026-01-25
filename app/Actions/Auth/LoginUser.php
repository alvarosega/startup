<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginData;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function execute(LoginData $data): array
    {
        // 1. Buscar Usuario por TELEFONO
        $user = User::where('phone', $data->phone)->first();

        // 2. Validar
        if (! $user || ! Hash::check($data->password, $user->password)) {
            usleep(rand(100000, 300000));
            throw ValidationException::withMessages([
                'phone' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        // 3. Validar Estado
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta ha sido desactivada por administración.'],
            ]);
        }

        // 4. Auditoría (REQUISITO: Auditoría Completa)
        LoginHistory::create([
            'user_id' => $user->id, // Eloquent maneja el cast a UUID
            'ip_address' => $data->ipAddress,
            'user_agent' => $data->userAgent,
            'device_fingerprint' => $data->deviceName, // O un hash real si implementas fingerprinting frontend
            'login_at' => now(),
        ]);

        // 5. Actualizar Metadata Usuario
        $user->update(['last_login_at' => now()]);

        // 6. Generar Token (Sanctum)
        // Definimos habilidades según el rol (Super Admin lo puede todo)
        $abilities = $user->hasRole('super_admin') ? ['*'] : ['access-platform'];
        
        $token = $user->createToken($data->deviceName, $abilities)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}