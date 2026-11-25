<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Staff;

use App\DTOs\Admin\User\StaffData;
use App\Models\Users\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateStaffAction
{
    public function execute(StaffData $data): Admin
    {
        return DB::transaction(function () use ($data): Admin {
            /** @var Admin $admin */
            $admin = Admin::create([
                'first_name' => $data->firstName,
                'last_name'  => $data->lastName,
                'phone'      => $data->phone,
                'email'      => $data->email,
                'password'   => Hash::make($data->password),
                'branch_id'  => $data->branchId,
                'is_active'  => $data->isActive,
            ]);

            if (!empty($data->roles)) {
                $admin->syncRoles($data->roles);
            }

            if (!empty($data->permissions)) {
                $admin->syncPermissions($data->permissions);
            }

            return $admin;
        });
    }
}
