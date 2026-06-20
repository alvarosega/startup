<?php

namespace App\DTOs\Driver\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class UploadDocsData
{
    public function __construct(
        public ?UploadedFile $ciFront,
        public ?UploadedFile $licensePhoto,
        public ?UploadedFile $vehiclePhoto,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ciFront: $request->file('ci_front'),
            licensePhoto: $request->file('license_photo'),
            vehiclePhoto: $request->file('vehicle_photo'),
        );
    }
}