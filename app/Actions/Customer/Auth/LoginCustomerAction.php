<?php

namespace App\Actions\Customer\Auth;

use App\DTOs\Customer\Auth\LoginCustomerData; // <--- CORREGIDO
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginCustomerAction
{
    /**
     * Ejecuta el intento de autenticación contra el guard de Customer.
     */
    public function execute(LoginCustomerData $data): void // <--- CORREGIDO
    {
        Log::info('[LOGIN] Intento de entrada', ['phone' => $data->phone]);

        $attempt = Auth::guard('customer')->attempt(
            ['phone' => $data->phone, 'password' => $data->password], 
            $data->remember
        );

        if (!$attempt) {
            Log::warning('[LOGIN] Credenciales inválidas', ['phone' => $data->phone]);
            
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales proporcionadas no son correctas.',
            ]);
        }
    }
}