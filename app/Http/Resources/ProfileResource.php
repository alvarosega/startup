<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['profile', 'driverProfile']);

        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'avatar' => [
                'type' => $this->avatar_type,
                'source' => $this->avatar_source,
                'url' => $this->avatar_type === 'custom' 
                    ? route('avatar.download', ['filename' => basename($this->avatar_source)]) 
                    : "/assets/avatars/{$this->avatar_source}",
            ],
            'personal_info' => [
                'first_name' => $this->profile->first_name ?? '',
                'last_name'  => $this->profile->last_name ?? '',
                'full_name'  => ($this->profile->first_name ?? '') . ' ' . ($this->profile->last_name ?? ''),
                // AGREGADOS:
                'birth_date' => $this->profile->birth_date ?? '',
                'gender'     => $this->profile->gender ?? '',
                'is_verified' => (bool) $this->profile->is_identity_verified,
            ],
            'driver_info' => $this->when($this->hasRole('driver'), function () {
                return [
                    'vehicle_type' => $this->driverProfile->vehicle_type ?? 'N/A',
                    'license_plate' => $this->driverProfile->license_plate ?? 'N/A',
                    'status' => $this->driverProfile->status ?? 'pending',
                ];
            }),
            'role' => $this->roles->first()?->name,
        ];
    }
}