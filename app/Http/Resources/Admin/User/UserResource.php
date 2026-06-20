<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => trim("{$this->first_name} {$this->last_name}"),
            'email'     => $this->email,
            'phone'     => $this->phone,
            // CORRECCIÓN: Acceso seguro a la relación branch (si existe) o campo directo
            'branch'    => $this->branch?->name ?? 'ZONA_NO_ASIGNADA', 
            'is_active' => (bool) $this->is_active,
            'avatar'    => [
                'type'   => $this->avatar_type ?? 'icon',
                'source' => $this->avatar_type === 'image' 
                            ? asset('storage/' . $this->avatar_source) 
                            : ($this->avatar_source ?? 'avatar_1.png'),
            ],
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
        ];
    }
}