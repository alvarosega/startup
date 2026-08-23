<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\DTOs\Admin\Auth\LoginAdminData;
use App\Models\Users\Admin;
use App\Models\Users\AdminAccessLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoginAdminAction
{
    public function execute(LoginAdminData $data): bool
    {
        $now = Carbon::now();
        $adminAttempted = Admin::where('email', $data->email)->first();

        // 1. Intento de autenticación atómica
        if (!Auth::guard('super_admin')->attempt([
            'email'     => $data->email,
            'password'  => $data->password,
            'is_active' => true,
        ])) {
            AdminAccessLog::create([
                'admin_id'        => $adminAttempted?->id,
                'email_attempted' => $data->email,
                'ip_address'      => $data->ip,
                'user_agent'      => $data->userAgent,
                'status'          => $adminAttempted && !$adminAttempted->is_active ? 'failed_inactive' : 'failed_credentials',
                'created_at'      => $now,
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var Admin $admin */
        $admin = Auth::guard('super_admin')->user();

        // 2. Control de rol
        if (!$admin->hasRole('super_admin')) {
            Auth::guard('super_admin')->logout();

            AdminAccessLog::create([
                'admin_id'        => $admin->id,
                'email_attempted' => $data->email,
                'ip_address'      => $data->ip,
                'user_agent'      => $data->userAgent,
                'status'          => 'failed_role',
                'created_at'      => $now,
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 3. Sesión Única (Purga de sesiones previas concurrentes)
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $admin->id)
                ->where('id', '!=', session()->getId())
                ->delete();
        }

        // 4. Actualización y registro exitoso
        $admin->update([
            'last_login_at' => $now,
            'last_seen_at'  => $now,
        ]);

        AdminAccessLog::create([
            'admin_id'        => $admin->id,
            'email_attempted' => $data->email,
            'ip_address'      => $data->ip,
            'user_agent'      => $data->userAgent,
            'status'          => 'success',
            'created_at'      => $now,
        ]);

        return true;
    }
}