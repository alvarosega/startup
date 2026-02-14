<?php

namespace App\Actions\Customer\Auth;

use App\DTOs\Customer\Auth\LoginData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // <--- IMPORTANTE

class LoginCustomerAction
{
    public function execute(LoginData $data): void
    {
        Log::info('[LOGIN DEBUG] 1. Iniciando intento', ['phone' => $data->phone]);

        // Intentamos loguear
        $attempt = Auth::guard('web')->attempt(
            ['phone' => $data->phone, 'password' => $data->password], 
            $data->remember
        );

        if (!$attempt) {
            Log::error('[LOGIN DEBUG] 2. Auth::attempt falló. Contraseña incorrecta o usuario no encontrado.');
            throw ValidationException::withMessages([
                'phone' => __('auth.failed'),
            ]);
        }

        Log::info('[LOGIN DEBUG] 2. Auth::attempt ÉXITO. Usuario autenticado internamente.');
        
        // Verificamos qué ID tiene el usuario recién logueado
        $user = Auth::guard('web')->user();
        Log::info('[LOGIN DEBUG] 3. ID del usuario en memoria:', ['id' => $user->id, 'id_bin' => bin2hex($user->getAuthIdentifier())]);
    }
}