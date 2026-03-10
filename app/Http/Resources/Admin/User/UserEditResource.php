<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $defaultAddress = $this->addresses->first();

        return [
            'id'         => (string) $this->id,
            'first_name' => (string) ($this->profile?->first_name ?? ''),
            'last_name'  => (string) ($this->profile?->last_name ?? ''),
            'email'      => (string) $this->email,
            'phone'      => (string) $this->phone,
            'branch_id'  => $this->branch_id ? (string) $this->branch_id : null,
            'is_active'  => (bool) $this->is_active,
            
            // Datos Geográficos (Nuevos)
            'address'    => $defaultAddress ? (string) $defaultAddress->address : '',
            'latitude'   => $defaultAddress ? (float) $defaultAddress->latitude : -16.5000,
            'longitude'  => $defaultAddress ? (float) $defaultAddress->longitude : -68.1500,
        ];
    }
}