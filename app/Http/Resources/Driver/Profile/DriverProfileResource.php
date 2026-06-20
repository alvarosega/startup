<?php

declare(strict_types=1);

namespace App\Http\Resources\Driver\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasCi = (bool) $this->profile?->ci_front_path;
        $hasLicense = (bool) $this->profile?->license_photo_path;

        return [
            'id'           => $this->id,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'status'       => $this->status, // pending, approved, suspended
            'has_all_docs' => $hasCi && $hasLicense,
            'branch'       => [
                'name' => $this->branch?->name ?? 'No asignada',
            ],
            'profile'      => [
                'first_name'     => $this->profile?->first_name,
                'last_name'      => $this->profile?->last_name,
                'license_plate'  => $this->profile?->license_plate,
                'vehicle_type'   => $this->profile?->vehicle_type,
                'has_ci'         => $hasCi,
                'has_license'    => $hasLicense,
            ]
        ];
    }
}