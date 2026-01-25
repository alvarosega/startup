<?php

namespace App\Actions\Profile;

use App\DTOs\Profile\UpdateAvatarData;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
// Si usas Intervention Image, impórtalo aquí. Usaré fallback nativo para compatibilidad.

class UpdateUserAvatar
{
    public function execute(User $user, UpdateAvatarData $data): User
    {
        // CASO A: Icono predefinido
        if ($data->type === 'icon') {
            $user->update([
                'avatar_type' => 'icon',
                'avatar_source' => $data->source
            ]);
            return $user;
        }

        // CASO B: Archivo personalizado
        if ($data->type === 'custom' && $data->file) {
            $filename = 'avatar_' . time() . '.' . $data->file->getClientOriginalExtension();
            
            // Guardar en disco 'private' o 'public' según tu config. Usamos 'private' por seguridad.
            $path = $data->file->storeAs("avatars/{$user->id}", $filename, 'private');

            // Borrar foto anterior si existía y era custom
            if ($user->avatar_type === 'custom' && $user->avatar_source) {
                Storage::disk('private')->delete($user->avatar_source);
            }

            $user->update([
                'avatar_type' => 'custom',
                'avatar_source' => $path
            ]);
        }

        return $user;
    }
}