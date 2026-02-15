<?php

namespace App\Actions\Admin\Auth;

use App\DTOs\Admin\Auth\LoginAdminData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginAdminAction
{
    public function execute(LoginAdminData $data): void
    {
        Log::info('[AdminLogin] Check Credentials', ['email' => $data->email]);

        // 1. CORRECCIÓN: Guard 'super_admin'
        if (! Auth::guard('super_admin')->attempt(
            ['email' => $data->email, 'password' => $data->password],
            $data->remember
        )) {
            Log::error('[AdminLogin] Failed', ['email' => $data->email]);
            
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden.',
            ]);
        }

        // 2. CORRECCIÓN: Guard 'super_admin' para obtener el usuario
        $admin = Auth::guard('super_admin')->user();
        
        if (! $admin->is_active) {
            Auth::guard('super_admin')->logout();
            throw ValidationException::withMessages([
                'email' => 'Cuenta desactivada.',
            ]);
        }

        // 3. Update Meta
        $admin->update(['last_login_at' => now()]);
        
        Log::info('[AdminLogin] Success', ['id' => $admin->id]);
    }
}