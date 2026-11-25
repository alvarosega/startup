<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Staff;

use App\DTOs\Admin\User\StaffData;
use App\Models\Users\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UpdateStaffAction
{
    public function execute(Admin $admin, StaffData $data): Admin
    {
        return DB::transaction(function () use ($admin, $data): Admin {
            $payload = [
                'first_name' => $data->firstName,
                'last_name'  => $data->lastName,
                'phone'      => $data->phone,
                'email'      => $data->email,
                'branch_id'  => $data->branchId,
                'is_active'  => $data->isActive,
            ];

            if (!empty($data->password)) {
                $payload['password'] = Hash::make($data->password);
            }

            $admin->update($payload);

            $admin->syncRoles($data->roles);
            $admin->syncPermissions($data->permissions);

            return $admin->fresh(['branch', 'roles', 'permissions']);
        });
    }
}
