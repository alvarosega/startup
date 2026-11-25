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
                'status'          => $adminAttempted && !$adminAttempted->is_active ? 'FAILED_INACTIVE' : 'INVALID_CREDENTIALS',
                'created_at'      => $now,
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var Admin $admin */
        $admin = Auth::guard('super_admin')->user();

        // 2. Control de rol administrativo
        $validRoles = ['super_admin', 'branch_manager', 'branch_warehouse', 'branch_logistics', 'branch_cashier'];
        if (!$admin->hasAnyRole($validRoles)) {
            Auth::guard('super_admin')->logout();

            AdminAccessLog::create([
                'admin_id'        => $admin->id,
                'email_attempted' => $data->email,
                'ip_address'      => $data->ip,
                'user_agent'      => $data->userAgent,
                'status'          => 'FAILED_ROLE',
                'created_at'      => $now,
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 3. Control de alcance por sucursal:
        // Si no es super_admin, DEBE tener una sucursal asignada y activa
        if (!$admin->hasRole('super_admin')) {
            if (!$admin->branch_id || !$admin->branch || !$admin->branch->is_active) {
                Auth::guard('super_admin')->logout();

                AdminAccessLog::create([
                    'admin_id'        => $admin->id,
                    'email_attempted' => $data->email,
                    'ip_address'      => $data->ip,
                    'user_agent'      => $data->userAgent,
                    'status'          => 'FAILED_BRANCH_INACTIVE',
                    'created_at'      => $now,
                ]);

                throw ValidationException::withMessages([
                    'email' => 'El administrador no tiene una sucursal activa asignada.',
                ]);
            }
        }

        // 4. Sesión Única (Purga de sesiones concurrentes)
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
            'status'          => 'SUCCESS',
            'created_at'      => $now,
        ]);

        return true;
    }
}