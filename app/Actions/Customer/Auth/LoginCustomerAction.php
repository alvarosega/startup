<?php

namespace App\Actions\Customer\Auth;

use App\DTOs\Customer\Auth\LoginCustomerData; // <--- CORREGIDO
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginCustomerAction
{
    public function execute(LoginCustomerData $data): bool
    {
        // IMPORTANTE: El login debe intentar autenticar con el teléfono completo
        // tal cual se guardó en la DB (+5178710820)
        if (!Auth::guard('customer')->attempt([
            'phone'    => $data->phone, 
            'password' => $data->password
        ], $data->remember)) {
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }
    
        return true;
    }
}