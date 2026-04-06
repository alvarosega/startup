<?php

namespace App\Http\Resources\Admin\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverEditResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'branch_id' => $this->branch_id,
            'phone'     => (string) $this->phone,
            'email'     => (string) $this->email,
            'status'    => (string) $this->status,
            'profile'   => [
                'first_name'     => $this->profile?->first_name,
                'last_name'      => $this->profile?->last_name,
                'license_number' => $this->profile?->license_number,
                'license_plate'  => $this->profile?->license_plate,
                'vehicle_type'   => $this->profile?->vehicle_type,
                'ci_front_path'  => $this->formatPrivateUrl($this->profile?->ci_front_path),
                // RECTIFICACIÓN: El nombre del atributo en el modelo es license_photo_path
                'license_path'   => $this->formatPrivateUrl($this->profile?->license_photo_path), 
            ]
        ];
    }
    private function formatPrivateUrl(?string $fullPath): ?string
    {
        if (!$fullPath) return null;
        
        // Extraemos solo el nombre del archivo (ej: drivers/uuid/docs/img.png -> img.png)
        $fileName = basename($fullPath);
        
        return route('admin.drivers.documents.show', [
            'driver' => $this->id,
            'path'   => $fileName
        ]);
    }
}