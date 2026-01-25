<?php

namespace App\Actions\Identity;

use App\DTOs\Identity\ProfileData;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateProfile
{
    public function execute(User $user, ProfileData $data): void
    {
        DB::transaction(function () use ($user, $data) {
            
            // --- PARTE A: Datos de Identidad (user_profiles) ---
            
            // 1. Obtenemos o creamos el perfil (si no existe)
            $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

            // 2. REGLA DE NEGOCIO (EL CANDADO):
            // Si NO está verificado, actualizamos nombres/fechas.
            // Si YA está verificado, IGNORAMOS estos campos por seguridad.
            if (! $profile->is_identity_verified) {
                $profile->update($data->toArrayForProfile());
            }

            // --- PARTE B: Datos de Cuenta (users) ---

            // 3. Email (Siempre editable, pero con consecuencias)
            if ($data->email && $data->email !== $user->email) {
                $user->email = $data->email;
                $user->email_verified_at = null; // Invalida la verificación actual
                $user->save();
            }
        });
    }
}