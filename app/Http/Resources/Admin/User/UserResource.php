<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => trim("{$this->first_name} {$this->last_name}"),
            'email'         => $this->email,
            'phone'         => $this->phone,
            'branch'        => $this->branch_name ?? 'Sin Sucursal',
            'is_active'     => (bool) $this->is_active,
            // --- NUEVA LÓGICA DE AVATAR ---
            'avatar' => [
                'type'   => $this->avatar_type, // 'icon' o 'image'
                'source' => $this->avatar_type === 'image' 
                            ? asset('storage/' . $this->avatar_source) 
                            : $this->avatar_source, // Si es icon, enviamos el nombre del .svg o clase
            ],
            'created_at'    => $this->created_at ? date('d/m/Y', strtotime($this->created_at)) : null,
        ];
    }
}