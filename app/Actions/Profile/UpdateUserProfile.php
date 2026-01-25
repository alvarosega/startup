<?php

namespace App\Actions\Profile;

use App\DTOs\Profile\UpdateProfileData;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserProfile
{
    public function execute(User $user, UpdateProfileData $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Actualizar Email en tabla Users (si cambió)
            if ($user->email !== $data->email) {
                $user->email = $data->email;
                $user->email_verified_at = null; // Resetear verificación si cambia email
                $user->save();
            }

            // 2. Actualizar Datos Personales en UserProfile
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data->firstName,
                    'last_name'  => $data->lastName,
                    'birth_date' => $data->birthDate,
                    'gender'     => $data->gender,
                ]
            );

            return $user->refresh();
        });
    }
}