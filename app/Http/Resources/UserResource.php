<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Trait HasPrefixedId se encarga de esto automáticamente,
            // pero lo forzamos aquí para ser explícitos.
            'id' => $this->getPrefixedId(), 
            
            'first_name' => $this->profile?->first_name,
            'last_name' => $this->profile?->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url, // Usando el Accessor que definiste
            'role' => $this->getRoleNames()->first(), // Spatie
            'is_active' => $this->is_active,
            
            // Datos condicionales (Solo si es el propio usuario)
            $this->mergeWhen($request->user()?->is($this->resource), [
                'completion_percentage' => $this->completion_percentage,
                'billing_info' => $this->billingInfos,
            ]),
        ];
    }
}