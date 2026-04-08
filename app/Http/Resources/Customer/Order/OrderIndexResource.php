<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            'code'          => (string) $this->code,
            'status'        => (string) $this->status,
            'delivery_type' => (string) $this->delivery_type,
            'total_amount'  => (float) $this->total_amount,
            'created_at'    => $this->created_at->toIso8601String(),
        ];
    }
}