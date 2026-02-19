<?php

namespace App\Http\Resources\Admin; // <--- Ruta correcta

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id, // UUID String
            'name'       => $this->name ?? trim("{$this->first_name} {$this->last_name}"),
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role_key'   => $this->role_key,
            'role_label' => $this->role_label,
            'type'       => $this->type,
            'branch'     => $this->branch_name ?? $this->branch?->name ?? 'Sin Sucursal',
            'is_active'  => (bool)$this->is_active,
        ];
    }
}