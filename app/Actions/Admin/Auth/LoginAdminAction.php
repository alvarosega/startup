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

        // 1. Auth Attempt (Guard: admin)
        if (! Auth::guard('admin')->attempt(
            ['email' => $data->email, 'password' => $data->password],
            $data->remember
        )) {
            Log::error('[AdminLogin] Failed', ['email' => $data->email]);
            
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden.',
            ]);
        }

        // 2. Check Active Status
        $admin = Auth::guard('admin')->user();
        
        if (! $admin->is_active) {
            Auth::guard('admin')->logout();
            throw ValidationException::withMessages([
                'email' => 'Cuenta desactivada.',
            ]);
        }

        // 3. Update Meta
        $admin->update(['last_login_at' => now()]);
        
        Log::info('[AdminLogin] Success', ['id' => $admin->id]);
    }
}