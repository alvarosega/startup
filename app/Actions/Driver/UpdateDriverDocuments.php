<?php

namespace App\Actions\Driver;

use App\DTOs\Driver\DriverDocumentsData;
use App\Models\User;
use App\Models\DriverProfile;
use Illuminate\Support\Facades\Storage;

class UpdateDriverDocuments
{
    public function execute(User $user, DriverDocumentsData $data): DriverProfile
    {
        // 1. Obtener o Crear Perfil (Patrón Singleton por Usuario)
        $profile = $user->driverProfile ?? new DriverProfile(['user_id' => $user->id]);

        // 2. Subir Imágenes (Si existen)
        if ($data->ciFront) {
            if ($profile->ci_front_path) Storage::disk('public')->delete($profile->ci_front_path);
            $profile->ci_front_path = $data->ciFront->store('drivers/docs', 'public');
        }

        if ($data->licensePhoto) {
            if ($profile->license_photo_path) Storage::disk('public')->delete($profile->license_photo_path);
            $profile->license_photo_path = $data->licensePhoto->store('drivers/docs', 'public');
        }

        if ($data->vehiclePhoto) {
            if ($profile->vehicle_photo_path) Storage::disk('public')->delete($profile->vehicle_photo_path);
            $profile->vehicle_photo_path = $data->vehiclePhoto->store('drivers/docs', 'public');
        }

        // 3. Cambiar estado a pendiente si se subieron documentos críticos
        if ($data->ciFront || $data->licensePhoto) {
            $profile->status = 'pending';
        }

        $profile->save();

        return $profile;
    }
}