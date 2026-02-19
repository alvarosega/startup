<?php
namespace App\Http\Resources\Admin\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'city'             => $this->city,
            'phone'            => $this->phone,
            'address'          => $this->address,
            'latitude'         => (float) $this->latitude,
            'longitude'        => (float) $this->longitude,
            'coverage_polygon' => $this->coverage_polygon,
            'opening_hours'    => $this->opening_hours,
            'is_active'        => (bool) $this->is_active,
            'created_at'       => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}