<?php
namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => (string) $this->id,
            'phone' => (string) $this->phone,
            'email' => mb_convert_encoding($this->email, 'UTF-8'),
            'profile' => [
                'first_name' => (string) $this->profile->first_name,
                'last_name'  => (string) $this->profile->last_name,
                'full_name'  => (string) "{$this->profile->first_name} {$this->profile->last_name}",
                'avatar'     => (string) $this->profile->avatar_source,
            ],
            'branch_id' => (string) $this->branch_id,
            'is_active' => (bool) $this->is_active,
        ];
    }
}