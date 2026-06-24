<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Users\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'is_online' => $this->is_online,
            'is_available' => $this->is_available,
            'was_previously_deleted' => $this->was_previously_deleted,
            'needs_password_change' => $this->needs_password_change,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),

            // Relación de Sucursal
            'branch' => $this->whenLoaded('branch', function () {
                return $this->branch ? [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ] : null;
            }),

            // Relación de Perfil
            'profile' => $this->whenLoaded('profile', function () {
                return $this->profile ? [
                    'first_name' => $this->profile->first_name,
                    'last_name' => $this->profile->last_name,
                    'license_number' => $this->profile->license_number,
                    'license_plate' => $this->profile->license_plate,
                    'vehicle_type' => $this->profile->vehicle_type,
                    'avatar_type' => $this->profile->avatar_type,
                    'avatar_source' => $this->profile->avatar_source,
                    'rejection_reason' => $this->profile->rejection_reason,
                    'ci_front_url' => $this->profile->ci_front_path, 
                    'license_photo_url' => $this->profile->license_photo_path,
                    'vehicle_photo_url' => $this->profile->vehicle_photo_path,
                ] : null;
            }),

            // Relación de Datos de Facturación
            'billing_infos' => $this->whenLoaded('billingInfos', function () {
                return $this->billingInfos->map(fn($info) => [
                    'id' => $info->id,
                    'nit_number' => $info->nit_number,
                    'business_name' => $info->business_name,
                ]);
            }),
        ];
    }
}