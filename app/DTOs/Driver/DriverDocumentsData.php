<?php

namespace App\DTOs\Driver;

use Illuminate\Http\UploadedFile;

class DriverDocumentsData
{
    public function __construct(
        public readonly ?UploadedFile $ciFront,
        public readonly ?UploadedFile $licensePhoto,
        public readonly ?UploadedFile $vehiclePhoto,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            ciFront: $request->file('ci_front'),
            licensePhoto: $request->file('license_photo'),
            vehiclePhoto: $request->file('vehicle_photo'),
        );
    }
}