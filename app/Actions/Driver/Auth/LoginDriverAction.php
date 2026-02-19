<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\LoginDriverData;
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\Validation\ValidationException;

class LoginDriverAction
{
    public function execute(LoginDriverData $data): void
    {
        Log::debug('[LoginAction] Ejecutando attempt en guard driver');

        $attempt = Auth::guard('driver')->attempt(
            ['phone' => $data->phone, 'password' => $data->password],
            $data->remember
        );

        if (!$attempt) {
            Log::warning('[LoginAction] Credenciales no coinciden en DB');
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales de conductor son incorrectas.',
            ]);
        }

        Log::debug('[LoginAction] Attempt devuelto true');
    }
}