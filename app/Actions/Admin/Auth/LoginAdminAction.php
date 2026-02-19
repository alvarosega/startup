<?php

namespace App\Actions\Admin\Auth;

use App\DTOs\Admin\Auth\LoginAdminData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginAdminAction
{
    // Dentro de LoginAdminAction.php
    public function execute(LoginAdminData $data): bool
    {
        // Laravel manejará el UUID String automáticamente 
        // al buscar en la tabla 'admins' si el modelo tiene HasUuids.
        if (!Auth::guard('super_admin')->attempt([
            'email' => $data->email,
            'password' => $data->password
        ], $data->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return true;
    }
}