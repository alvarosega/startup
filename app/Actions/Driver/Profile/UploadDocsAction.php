<?php

namespace App\Actions\Driver\Profile;

use App\DTOs\Driver\Profile\UploadDocsData;
use App\Models\Driver;
use Illuminate\Support\Facades\Storage;

class UploadDocsAction
{
    public function execute(UploadDocsData $data, Driver $driver): void
    {
        $details = $driver->details;
        $path = "drivers/{$driver->id}/docs";

        if ($data->ciFront) {
            if ($details->ci_front_path) Storage::disk('public')->delete($details->ci_front_path);
            $details->ci_front_path = $data->ciFront->store($path, 'public');
        }

        if ($data->licensePhoto) {
            if ($details->license_photo_path) Storage::disk('public')->delete($details->license_photo_path);
            $details->license_photo_path = $data->licensePhoto->store($path, 'public');
        }

        if ($data->vehiclePhoto) {
            if ($details->vehicle_photo_path) Storage::disk('public')->delete($details->vehicle_photo_path);
            $details->vehicle_photo_path = $data->vehiclePhoto->store($path, 'public');
        }

        // Si sube documentos, el estado vuelve a pendiente de revisión obligatoriamente
        $details->verification_status = 'pending';
        $details->save();
    }
}