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
            'branch_id' => $this->branch_id ? (string) $this->branch_id : null,
            'phone'     => (string) $this->phone,
            'email'     => (string) $this->email,
            'status'    => (string) $this->status,
            'is_online' => (bool) $this->is_online,
            
            'profile'   => [
                'first_name'     => (string) ($this->profile?->first_name ?? ''),
                'last_name'      => (string) ($this->profile?->last_name ?? ''),
                'license_number' => (string) ($this->profile?->license_number ?? ''),
                'license_plate'  => (string) ($this->profile?->license_plate ?? ''),
                'vehicle_type'   => (string) ($this->profile?->vehicle_type ?? 'moto'),
                
                // URLs públicas para que el Admin verifique los documentos
                'ci_front_path'      => $this->formatUrl($this->profile?->ci_front_path),
                'license_photo_path' => $this->formatUrl($this->profile?->license_photo_path),
                'vehicle_photo_path' => $this->formatUrl($this->profile?->vehicle_photo_path),
                'rejection_reason'   => $this->profile?->rejection_reason,
            ]
        ];
    }

    private function formatUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset('storage/' . $path);
    }
}