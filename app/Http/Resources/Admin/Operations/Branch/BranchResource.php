<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Operations\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                          => (string) $this->id,
            'name'                        => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'city'                        => (string) $this->city,
            'phone'                       => $this->phone,
            'address'                     => $this->address,
            'latitude'                    => (float) $this->latitude,
            'longitude'                   => (float) $this->longitude,
            'coverage_polygon'            => $this->coverage_polygon,
            'opening_hours'               => $this->opening_hours,
            'is_active'                   => (bool) $this->is_active,
            'is_default'                  => (bool) $this->is_default,
            'delivery_base_fee'           => (float) $this->delivery_base_fee,
            'delivery_price_per_km'       => (float) $this->delivery_price_per_km,
            'surge_multiplier'            => (float) $this->surge_multiplier,
            'min_order_amount'            => (float) $this->min_order_amount,
            'small_order_fee'             => (float) $this->small_order_fee,
            'base_service_fee_percentage' => (float) $this->base_service_fee_percentage,
            'created_at'                  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}