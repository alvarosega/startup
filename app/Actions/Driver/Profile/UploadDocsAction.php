<?php

namespace App\Actions\Driver\Profile;

use App\DTOs\Driver\Profile\UploadDocsData;
use App\Models\Driver;
use Illuminate\Support\Facades\Storage;

class UploadDocsAction
{
    public function execute(UploadDocsData $data, Driver $driver): void
    {
        $profile = $driver->profile; // RECTIFICADO: Usar 'profile'
        $path = "drivers/{$driver->id}/documents";

        if ($data->ciFront) {
            if ($profile->ci_front_path) Storage::disk('private')->delete($profile->ci_front_path);
            $profile->ci_front_path = $data->ciFront->store($path, 'private');
        }

        if ($data->licensePhoto) {
            if ($profile->license_photo_path) Storage::disk('private')->delete($profile->license_photo_path);
            $profile->license_photo_path = $data->licensePhoto->store($path, 'private');
        }

        $profile->save();

        // Al subir documentos nuevos, el status vuelve a revisión
        $driver->update(['status' => 'pending']);
    }
}