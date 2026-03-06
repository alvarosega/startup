<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Forzamos el mapeo desde la relación 'profile'
        return [
            'id'         => $this->id,
            'first_name' => $this->profile?->first_name ?? '',
            'last_name'  => $this->profile?->last_name ?? '',
            'email'      => $this->email,
            'phone'      => $this->phone,
            'branch_id'  => $this->branch_id,
            'is_active'  => (bool) $this->is_active,
        ];
    }
}