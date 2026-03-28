<?php

namespace App\Http\Resources\Customer\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            'email'         => (string) $this->email,
            'phone'         => (string) $this->phone,
            'country_code'  => (string) $this->country_code,
            'is_active'     => (bool) $this->is_active,
            'profile' => [
                'first_name' => (string) $this->profile->first_name,
                'last_name'  => (string) $this->profile->last_name,
                'full_name'  => (string) "{$this->profile->first_name} {$this->profile->last_name}",
                'avatar_url' => $this->resolveAvatarUrl(),
                'avatar_source' => (string) $this->profile->avatar_source,
                // CORRECCIÓN: Inyectar campos para el formulario
                'birth_date' => $this->profile->birth_date ? (string) $this->profile->birth_date : '',
                'gender'     => (string) ($this->profile->gender ?? 'prefer_not_to_say'),
            ],
            'branch_context' => [
                'id' => (string) $this->branch_id,
            ],
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ];
    }

    private function resolveAvatarUrl(): string
    {
        if ($this->profile->avatar_type === 'icon') {
            return asset("assets/avatars/{$this->profile->avatar_source}");
        }
        return Storage::disk('public')->url("avatars/uploads/{$this->profile->avatar_source}");
    }
}