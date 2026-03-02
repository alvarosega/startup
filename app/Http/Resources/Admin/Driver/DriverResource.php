<?php

namespace App\Http\Resources\Admin\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'branch_id'    => $this->branch_id, // <-- AÑADIDO
            'phone'        => $this->phone,
            'is_active'    => $this->status === 'active', 
            
            'full_name'    => $this->details ? trim("{$this->details->first_name} {$this->details->last_name}") : 'Sin Datos',
            'license_plate'=> $this->details?->license_plate ?? 'N/A',
            'vehicle_type' => $this->details?->vehicle_type ?? 'moto',
            'is_verified'  => ($this->details?->verification_status === 'verified'),

            // <-- AÑADIDO: Objeto de la sucursal para la vista Index
            'branch'       => $this->whenLoaded('branch', function () {
                return $this->branch ? [
                    'id'   => $this->branch->id,
                    'name' => $this->branch->name,
                ] : null;
            }),

            'details'      => [
                'first_name'          => $this->details?->first_name ?? '',
                'last_name'           => $this->details?->last_name ?? '',
                'license_number'      => $this->details?->license_number ?? '',
                'license_plate'       => $this->details?->license_plate ?? '',
                'vehicle_type'        => $this->details?->vehicle_type ?? 'moto',
                'verification_status' => $this->details?->verification_status ?? 'pending',
                'ci_front_path'       => $this->details?->ci_front_path,
                'license_photo_path'  => $this->details?->license_photo_path,
                'vehicle_photo_path'  => $this->details?->vehicle_photo_path,
            ],
        ];
    }
}