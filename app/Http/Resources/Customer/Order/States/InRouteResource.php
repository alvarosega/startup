<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Order\States;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InRouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'delivery_data' => $this->delivery_data, // Mapa estático del destino
            'delivery_otp' => $this->delivery_otp,   // PIN de 4 dígitos
            
            'driver' => $this->whenLoaded('driver', fn () => [
                'phone' => $this->driver->phone,
                'first_name' => $this->driver->profile?->first_name,
                'last_name' => $this->driver->profile?->last_name,
                'vehicle' => $this->driver->profile?->vehicle_type,
                'plate' => $this->driver->profile?->license_plate,
            ])
        ];
    }
}