<?php

namespace App\Actions\Admin\User;

use App\Models\Customer;
use App\DTOs\Admin\User\UpdateUserDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function execute(string $id, UpdateUserDTO $data): void
    {
        DB::transaction(function () use ($id, $data) {
            $customer = Customer::findOrFail($id);

            // 1. Actualizar tabla core: customers
            $updateData = [
                'email'     => $data->email,
                'phone'     => $data->phone,
                'branch_id' => $data->branchId,
                'is_active' => $data->isActive,
            ];

            if ($data->password) {
                $updateData['password'] = Hash::make($data->password);
            }

            $customer->update($updateData);

            // 2. Actualizar tabla dependiente: customer_profiles
            $customer->profile()->update([
                'first_name' => $data->firstName,
                'last_name'  => $data->lastName,
            ]);
        });
    }
}