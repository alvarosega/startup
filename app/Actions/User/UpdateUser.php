<?php

namespace App\Actions\User;

use App\DTOs\User\UserData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUser
{
    public function execute(User $user, UserData $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Preparar datos a actualizar
            $updateData = [
                'branch_id' => $data->branchId,
                'is_active' => $data->isActive,
                'phone' => $data->phone,
                'email' => $data->email,
            ];

            // Solo actualizar password si se envió uno nuevo
            if (!empty($data->password)) {
                $updateData['password'] = Hash::make($data->password);
            }

            $user->update($updateData);

            // 2. Actualizar Perfil
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data->firstName,
                    'last_name' => $data->lastName
                ]
            );

            // 3. Sincronizar Rol
            $user->roles()->sync([$data->roleId]);

            return $user;
        });
    }
}