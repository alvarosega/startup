<?php

namespace App\Http\Resources\Admin\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'branch_id'     => $this->branch_id,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'status'        => $this->status, // 'pending', 'active', etc.
            'is_online'     => (bool) $this->is_online,
            
            // Variables calculadas para la UI
            'is_active'     => $this->status === 'active', 
            'full_name'     => $this->profile ? trim("{$this->profile->first_name} {$this->profile->last_name}") : 'Sin Datos',
            'license_plate' => $this->profile?->license_plate ?? 'N/A',
            'vehicle_type'  => $this->profile?->vehicle_type ?? 'moto',

            'branch'        => $this->whenLoaded('branch', function () {
                return $this->branch ? [
                    'id'   => $this->branch->id,
                    'name' => $this->branch->name,
                ] : null;
            }),

            'profile'       => [
                'first_name'         => $this->profile?->first_name ?? '',
                'last_name'          => $this->profile?->last_name ?? '',
                'license_number'     => $this->profile?->license_number ?? '',
                'license_plate'      => $this->profile?->license_plate ?? '',
                'vehicle_type'       => $this->profile?->vehicle_type ?? 'moto',
                'ci_front_path'      => $this->profile?->ci_front_path,
                'license_photo_path' => $this->profile?->license_photo_path,
                'vehicle_photo_path' => $this->profile?->vehicle_photo_path,
            ],
        ];
    }
}