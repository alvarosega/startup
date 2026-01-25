<?php

namespace App\Actions\Identity;

use App\DTOs\Identity\AvatarData;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image; // Opcional, manejamos fallback

class UpdateAvatar
{
    public function execute(User $user, AvatarData $data): void
    {
        // CASO 1: Icono predefinido
        if ($data->type === 'icon') {
            $user->update([
                'avatar_type' => 'icon',
                'avatar_source' => $data->source
            ]);
            return;
        }

        // CASO 2: Imagen subida (Custom)
        if ($data->file) {
            // Borrar anterior si era custom
            if ($user->avatar_type === 'custom' && $user->avatar_source) {
                Storage::disk('public')->delete($user->avatar_source); // Usamos public o private según tu config
            }

            $filename = 'avatar_' . time() . '.webp';
            $path = "avatars/{$user->id}";

            // Lógica de optimización (Intervention Image) con Fallback
            if (class_exists(Image::class)) {
                try {
                    $image = Image::read($data->file);
                    $image->cover(300, 300); // Resize inteligente
                    $encoded = $image->toWebp(80);
                    
                    Storage::disk('public')->put("$path/$filename", (string) $encoded);
                    $finalPath = "$path/$filename";
                } catch (\Exception $e) {
                    // Si falla la conversión, guardamos normal
                    $finalPath = $data->file->store($path, 'public');
                }
            } else {
                // Guardado nativo sin procesamiento
                $finalPath = $data->file->store($path, 'public');
            }

            $user->update([
                'avatar_type' => 'custom',
                'avatar_source' => $finalPath
            ]);
        }
    }
}