<?php

namespace App\Http\Resources\Driver\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status, // pending, approved, suspended
            'branch' => [
                'name' => $this->branch?->name ?? 'No asignada',
            ],
            'profile' => [
                'first_name'     => $this->profile?->first_name,
                'last_name'      => $this->profile?->last_name,
                'license_plate'  => $this->profile?->license_plate,
                'vehicle_type'   => $this->profile?->vehicle_type,
                // No enviamos paths privados aquí por seguridad, solo si existen
                'has_ci'         => (bool) $this->profile?->ci_front_path,
                'has_license'    => (bool) $this->profile?->license_photo_path,
            ]
        ];
    }
}