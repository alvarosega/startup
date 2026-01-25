<?php

namespace App\Actions\User;

use App\DTOs\User\UserData;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function execute(UserData $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Limpieza de teléfono
            $phone = str_replace(' ', '', $data->phone);
            if (!str_starts_with($phone, '+')) $phone = '+591' . $phone;

            // 2. Crear Usuario Base
            $user = User::create([
                'phone' => $phone,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'branch_id' => $data->branchId,
                'is_active' => $data->isActive,
                'avatar_type' => 'icon',
                'avatar_source' => 'avatar_1.svg'
            ]);

            // 3. Crear Perfil
            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'is_identity_verified' => true // Admins nacen verificados
            ]);

            // 4. Asignar Rol
            $user->roles()->sync([$data->roleId]);

            return $user;
        });
    }
}