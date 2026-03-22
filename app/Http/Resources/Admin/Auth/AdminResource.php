<?php 

namespace App\Http\Resources\Admin\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,
            'first_name' => (string) $this->first_name,
            'last_name'  => (string) $this->last_name,
            'full_name'  => (string) "{$this->first_name} {$this->last_name}",
            'email'      => (string) $this->email,
            'roles'      => $this->getRoleNames(), // Spatie Trait
            'permissions' => [
                'manage_users'   => (bool) $this->can('manage_users'),
                'manage_drivers' => (bool) $this->can('manage_drivers'),
                'manage_catalog' => (bool) $this->can('manage_catalog'),
            ],
        ];
    }
}