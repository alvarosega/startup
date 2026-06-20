<?php

namespace App\Http\Resources\Driver\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => (string) $this->id,
            'email'  => (string) $this->email,
            'status' => (string) $this->status,
            'name'   => (string) "{$this->profile?->first_name} {$this->profile?->last_name}",
            'branch' => $this->branch_id ? [
                'id'   => (string) $this->branch_id,
                'name' => (string) $this->branch?->name,
            ] : null,
        ];
    }
}