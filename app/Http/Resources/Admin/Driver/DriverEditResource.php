<?php

namespace App\Http\Resources\Admin\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DriverEditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'branch_id' => $this->branch_id ? (string) $this->branch_id : null,
            'phone'     => (string) $this->phone,
            'email'     => (string) $this->email,
            'status'    => (string) $this->status, // 'pending', 'active', 'inactive', 'rejected'
            'is_online' => (bool) $this->is_online,
            
            'details' => [
                'first_name'     => (string) ($this->details?->first_name ?? ''),
                'last_name'      => (string) ($this->details?->last_name ?? ''),
                'license_number' => (string) ($this->details?->license_number ?? ''),
                'license_plate'  => (string) ($this->details?->license_plate ?? ''),
                'vehicle_type'   => (string) ($this->details?->vehicle_type ?? 'moto'),
                
                // Mapeo seguro de URLs (Evita que el frontend adivine rutas)
                'ci_front_path'      => $this->formatUrl($this->details?->ci_front_path),
                'license_photo_path' => $this->formatUrl($this->details?->license_photo_path),
                'vehicle_photo_path' => $this->formatUrl($this->details?->vehicle_photo_path),
            ]
        ];
    }

    private function formatUrl(?string $path): ?string
    {
        if (!$path) return null;
        // Si ya es una URL válida (S3, externa), la devolvemos tal cual
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        // Si es un path local, le añadimos el helper de storage
        return asset('storage/' . $path);
    }
}