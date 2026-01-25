<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterDriverData;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\DriverProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisterDriver
{
    public function execute(RegisterDriverData $data): User
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear Usuario
            $user = User::create([
                'phone' => $data->phone,
                'email' => $data->email, // <--- Guardar Email
                'password' => Hash::make($data->password),
                'is_active' => true,
                'avatar_type' => $data->avatarType,
                'avatar_source' => $data->avatarSource ?? 'avatar_1.svg',
                'email_verified_at' => null, // No verificado por defecto
            ]);

            // Avatar Custom
            if ($data->avatarType === 'custom' && $data->avatarFile) {
                $path = $data->avatarFile->store("avatars/{$user->id}", 'private');
                $user->update(['avatar_source' => $path]);
            }

            // 2. Asignar Rol
            $user->assignRole('driver');

            // 3. Crear Perfil Básico (Para nombre)
            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'is_identity_verified' => false,
            ]);

            // 4. Crear Perfil de Conductor (Datos Vehículo)
            DriverProfile::create([
                'user_id' => $user->id,
                'license_number' => $data->licenseNumber,
                'license_plate' => strtoupper($data->licensePlate),
                'vehicle_type' => $data->vehicleType,
                'status' => 'pending', // Siempre pendiente revisión
            ]);

            event(new Registered($user));

            return $user;
        });
    }
}