<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\LoginHistory; // Asumiendo que quieres auditar la salida también (Opcional pero recomendado)
use Illuminate\Support\Facades\Log;

class LogoutUser
{
    public function execute(User $user): void
    {
        // 1. Revocar Tokens (Higiene de Seguridad)
        // Esto asegura que si tenía un token de API activo, muera al salir de la web.
        $user->tokens()->delete();

        // 2. Auditoría (Opcional: registrar hora de salida)
        // Si no tienes columna 'logout_at' en login_history, podemos saltar esto o registrar un log simple.
        Log::info("User logged out", ['user_id' => $user->id, 'ip' => request()->ip()]);
    }
}