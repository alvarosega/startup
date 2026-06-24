<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Users\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'email' => $this->email,
            'trust_score' => $this->trust_score,
            'is_active' => $this->is_active,
            'was_previously_deleted' => $this->was_previously_deleted,
            'needs_password_change' => $this->needs_password_change,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
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
                    'birth_date' => $this->profile->birth_date?->format('Y-m-d'),
                    'gender' => $this->profile->gender,
                    'avatar_type' => $this->profile->avatar_type,
                    'avatar_source' => $this->profile->avatar_source,
                ] : null;
            }),

            // Relación de Direcciones
            'addresses' => $this->whenLoaded('addresses', function () {
                return $this->addresses->map(fn($address) => [
                    'id' => $address->id,
                    'alias' => $address->alias,
                    'address' => $address->address,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'reference' => $address->reference,
                    'is_default' => $address->is_default,
                ]);
            }),

            // Relación de Datos de Facturación
            'billing_infos' => $this->whenLoaded('billingInfos', function () {
                return $this->billingInfos->map(fn($info) => [
                    'id' => $info->id,
                    'nit_number' => $info->nit_number,
                    'business_name' => $info->business_name,
                    'is_default' => $info->is_default,
                ]);
            }),
        ];
    }
}